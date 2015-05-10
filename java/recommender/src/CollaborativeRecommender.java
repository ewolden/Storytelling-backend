import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Random;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.recommender.GenericRecommendedItem;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;

/*Contributors: Kjersti Fagerholt, Roar Gjøvaag, Ragnhild Krogh, Espen Strømjordet,
Audun Sæther, Hanne Marie Trelease, Eivind Halmøy Wolden

"Copyright 2015 The TAG CLOUD/SINTEF project

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License."
*/

/** 
 * Creates a list of recommendations for a user based on item based and user based collaborative filtering 
 * and inserts these recommendations into the database.
 * 
 * @author Audun Sæther
 * @author Kjersti Fagerholt 
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

public class CollaborativeRecommender {
	/** The userId for the user the recommendations are made for */
	public long userId;
	/** A list of the produced recommendations */
	public ArrayList<CollaborativeRecommendation> recommendations;
	/** Describes whether to add recommendations to existing ones, or to create new ones */
	String add;
	
	
	/**
	 * Constructor
	 * 
	 * @param userId			userId for the user the recommendations are made for
	 * @param add				describe whether we should add or create new recommendations
	 */
	public CollaborativeRecommender(long userId, String add) {
    	this.userId = userId;
    	this.add = add;
    }
	
	/**
	 * Method that creates a list of recommendations.
	 * Gets lists of recommendations from ItemRecommender.java and UserbasedRecommender.java and merges these two list to create one 
	 * list of recommendations. This list of recommendations is inserted into the database.
	 * Already rated items are excluded. Depending on the value of the field "add", items viewed at front end may also be excluded.
	 * 
	 * @return					the size of the list of recommendations
	 * @throws TasteException	thrown if there is a exception from Mahout
	 */
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
		db.setConnection();
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
		/* Take the top 10 recommendations and and prepare to insert them into database */
		ArrayList<DatabaseInsertObject> itemsToBeInserted = new ArrayList<>();
		ArrayList<Long> idsToBeInserted = new ArrayList<>();
		int ranking = 1;
		Random rand = new Random();
    	int randomDislikedRanking = rand.nextInt(6)+5;
		for(CollaborativeRecommendation recommendation : collaborativeRecommendations){	
			/* To get a story outside of the users preferences, finds the least recommended story */
    		if(randomDislikedRanking == ranking){
    			/*Make sure the false recommendation is not already in the front end array or already among the top ten recommendation (may happen if the user doesn't have many not seen/not rated stories left) */
    			for(int i=1; i<collaborativeRecommendations.size(); i++){
    				long dislikedStoryId = collaborativeRecommendations.get(collaborativeRecommendations.size() - i).getItem().getItemID();
    				if (!frontendStories.contains((int)dislikedStoryId) && !idsToBeInserted.contains(dislikedStoryId) && ratedStories.get((int)dislikedStoryId) == null){
    					itemsToBeInserted.add(new DatabaseInsertObject((int)userId, "DF."+dislikedStoryId, "FalseRecommendation", 1, 0, ranking,collaborativeRecommendations.get(collaborativeRecommendations.size() - i).getItem().getValue()));
    					idsToBeInserted.add(dislikedStoryId);
    					System.out.print("False recommend: ");
    					System.out.println(dislikedStoryId);    				
    					break;
    				}
    			}
    			ranking++;
    			if(ranking > 10){
    				break;
    			}
    			continue;
    		}
    		
    		/*If the item has not been rated,is not already in the recommendation list at front end or already a false recommendation we insert it*/
    		if ((ratedStories.get((int)recommendation.getItem().getItemID())==null) && !frontendStories.contains((int)recommendation.getItem().getItemID()) && !idsToBeInserted.contains(recommendation.getItem().getItemID())){
    			/*Get the 30 items that had most influence on the recommendation*/
    			if(recommendation.getExplanation().equals("item")){
    				List<RecommendedItem> becauseItems = IR.getRecommender().recommendedBecause(userId, recommendation.getItem().getItemID(), 30);
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
        			idsToBeInserted.add(recommendation.getItem().getItemID());
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
	
	
	/**
	 * Method that returns the list of recommendations
	 * 
	 * @return	the list of recommendations
	 */
	public ArrayList<CollaborativeRecommendation> getRecommendations(){
		return this.recommendations;
	}
}
