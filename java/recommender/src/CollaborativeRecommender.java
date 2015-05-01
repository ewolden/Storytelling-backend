import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Random;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.recommender.GenericRecommendedItem;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;


public class CollaborativeRecommender {
	public long userId;
	public ArrayList<CollaborativeRecommendation> recommendations;
	String add;
	
	public CollaborativeRecommender(long userId, String add) {
    	this.userId = userId;
    	this.add = add;
    }
	
	public int runCollaborativeRecommender() throws TasteException{
		/* itembased and userbased recommendations arrays initialized */
		ArrayList<CollaborativeRecommendation> itembased = new ArrayList<CollaborativeRecommendation>();
		ArrayList<CollaborativeRecommendation> userbased = new ArrayList<CollaborativeRecommendation>();
		ArrayList<CollaborativeRecommendation> itemremoved = new ArrayList<CollaborativeRecommendation>();
		ArrayList<CollaborativeRecommendation> userremoved = new ArrayList<CollaborativeRecommendation>();
		
		/* Both itembased and userbased will be collected to this arraylist */
		ArrayList<CollaborativeRecommendation> collaborativeRecommendations = new ArrayList<CollaborativeRecommendation>();
		
		/* Database setup */
		DatabaseConnection db = new DatabaseConnection("collaborative_view");
    	db.setDataModel();
    	DataModel model = db.getDataModel();
		
    	/* run the item and user recommenders */
		ItemRecommender IR = new ItemRecommender(userId);
		itembased = IR.RunItemRecommender(model);
		UserbasedRecommender UR = new UserbasedRecommender(userId);
		userbased = UR.RunUserbasedRecommender(model);

		/* Loop through all recommendations average result from user and item based, remove duplicates */
		for(CollaborativeRecommendation itemrecommendation : itembased){
			float average_recommender_value = 0;
			for(CollaborativeRecommendation userrecommendation : userbased){
				if(itemrecommendation.getItem().getItemID() == userrecommendation.getItem().getItemID()){
					/* Find the average value if both user and item based has the recommendation */
					average_recommender_value = (itemrecommendation.getItem().getValue() + userrecommendation.getItem().getValue())/2;
					/* Add to collaborative list and remove the recommendation from both lists */
					collaborativeRecommendations.add(new CollaborativeRecommendation(
							new GenericRecommendedItem(itemrecommendation.getItem().getItemID(),average_recommender_value), 
							itemrecommendation.getUserId(),
							"item and user based"));
					itemremoved.add(itemrecommendation);
					userremoved.add(userrecommendation);
				}
			}
		}
		
		/* remove duplicates present in both lists */
		for(CollaborativeRecommendation recommendation : itemremoved){
			itembased.remove(recommendation);
		}
		for(CollaborativeRecommendation recommendation : userremoved){
			userbased.remove(recommendation);
		}
		
		/* add results unique to each list */
		for(CollaborativeRecommendation recommendation : itembased){
			collaborativeRecommendations.add(recommendation);
		}
		for(CollaborativeRecommendation recommendation : userbased){
			collaborativeRecommendations.add(recommendation);
		}
		
		/* Sort the final results list */
		Collections.sort(collaborativeRecommendations, new CompareCollaborative());
		
		/*Find the stories that the user have rated*/
    	HashMap<Integer,Integer> ratedStories = db.getRated((int)userId);
    	ArrayList<Integer> frontendStories = new ArrayList<>();

    	/*Find the stories already present in the recommendations list at front end
    	 * These stories should not be recommended again*/
    	if(add.equals("true")){
    		frontendStories = db.getStoriesInFrontendArray((int) userId);
    	}
    	System.out.println("FrontendStories: "+frontendStories);
		/* Take the top 10 recommendations and and prepare to insert them into database */
		ArrayList<DatabaseInsertObject> itemsToBeInserted = new ArrayList<>();
		int ranking = 1;
		Random rand = new Random();
    	int randomDislikedRanking = rand.nextInt(6)+5;
		for(CollaborativeRecommendation recommendation : collaborativeRecommendations){	
			/* To get a story outside of the users preferences, finds the least recommended story */
    		if(randomDislikedRanking == ranking){
    			itemsToBeInserted.add(new DatabaseInsertObject((int)userId, "DF."+collaborativeRecommendations.get(collaborativeRecommendations.size() - 1).getItem().getItemID(), "FalseRecommendation", 1, 1, ranking,collaborativeRecommendations.get(collaborativeRecommendations.size() - 1).getItem().getValue()));
    			System.out.print("False recommend: ");
    			System.out.println(collaborativeRecommendations.get(collaborativeRecommendations.size() - 1).getItem());
    			ranking++;
    			continue;
    		}
    		
			/*If the item has not been rated or is not already in the recommendation list at front end we insert it*/
    		if ((ratedStories.get((int)recommendation.getItem().getItemID())==null) && !frontendStories.contains((int)recommendation.getItem().getItemID())){
    			/* To get which stories influenced this recommendation */
    			if(recommendation.getExplanation().equals("item")){
    				List<RecommendedItem> becauseItems = IR.getRecommender().recommendedBecause(userId, recommendation.getItem().getItemID(), model.getNumItems());
        			int counter = 1;
        			ArrayList<RecommendedItem> explanationItems = new ArrayList<>();
        			for (RecommendedItem because : becauseItems){ 
        				/*Add story to explanation if this story has been rated and the rating is good*/
        				if (!explanationItems.contains(because) && ratedStories.get((int)because.getItemID())!= null && ratedStories.get((int)because.getItemID())> 2){
        					explanationItems.add(because);
        					counter++;
        				}
        				if (counter>3){
        					break;
        				}
        			}
        			String explanation = db.createExplanation(explanationItems);
        			itemsToBeInserted.add(new DatabaseInsertObject((int)this.userId, "DF."+recommendation.getItem().getItemID(), explanation, 0, 1, ranking,recommendation.getItem().getValue()));
        			System.out.println(recommendation.getItem());
        			ranking++;
    			} else {
    				itemsToBeInserted.add(new DatabaseInsertObject((int)this.userId, "DF."+recommendation.getItem().getItemID(), recommendation.getExplanation(), 0, 1, ranking,recommendation.getItem().getValue()));
        			System.out.println(recommendation.getItem());
        			ranking++;
    			}
    			
    			if(ranking > 10){
    				break;
    			}
    		}
		}
		
		/* Put the list of all possible recommendations in the model */
		this.recommendations = collaborativeRecommendations;
		
		/*Delete the current recommendations stored in stored_story that has not been seen by the user*/
		db.deleteRecommendations((int)userId);
		
		/* Insert new recommendations into the database */
		db.insertUpdateRecommendValues(itemsToBeInserted);
		
		/* Close connection */
    	db.closeConnection();
    	
    	/* Return number of recommendations possible */
		return collaborativeRecommendations.size();
	}
	public ArrayList<CollaborativeRecommendation> getRecommendations(){
		return this.recommendations;
	}
}
