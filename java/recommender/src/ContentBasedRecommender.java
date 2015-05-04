import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.net.URISyntaxException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.List;
import java.util.Random;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.recommender.GenericItemBasedRecommender;
import org.apache.mahout.cf.taste.impl.similarity.GenericItemSimilarity;
import org.apache.mahout.cf.taste.impl.similarity.GenericItemSimilarity.ItemItemSimilarity;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.apache.mahout.cf.taste.similarity.ItemSimilarity;


public class ContentBasedRecommender
{
	File fileLocation = null;
	DatabaseConnection conn = null;
	long userId;
	List<RecommendedItem> recommendations;
	/*Tells us whether we should make brand new recommendations (="false")
	 * or if we should make recommendations of items that is not in the frontend array (="true")*/
	String add;
	
    public ContentBasedRecommender(long userId, String add) throws TasteException {
    	this.userId = userId;
    	this.add = add;
		conn = new DatabaseConnection("content"+userId);
    }
    
    public void runContentBasedRecommender() throws TasteException{
		try {
			fileLocation = new File(this.getClass().getProtectionDomain().getCodeSource().getLocation().toURI());
		} catch (URISyntaxException e) {
			e.printStackTrace();
		}
		/*"content"+userId is the name of the view we shall create*/
		conn.setConnection();
		
    	/*Create a temporary view the includes all preferences values for this user*/
    	conn.createView((int)userId);

    	/*Sets the dataModel based on the data in the created view*/
    	conn.setDataModel();

    	DataModel model = conn.getDataModel();
    	
    	/*Gets all the info from the similarites.csv file into a list of objects accepted by Mahout*/
    	Collection<ItemItemSimilarity> sim = getStorySimilarities();
    	
    	/*GenericItemBasedRecommender need an ItemSimilarity-object as input, so create an instance of this class.*/
    	ItemSimilarity similarity = new GenericItemSimilarity(sim);
    	
    	/*Create a new Recommender-instance with our datamodel and story similarities*/
    	GenericItemBasedRecommender recommender = new GenericItemBasedRecommender(model, similarity);
    	
    	/* Compute the recommendations. model.getNumItems() is the number of recommendations we want (we don't really want that many, 
    	 * but we don't know how many of the top items the user already have rated), don't worry about the null, 
    	 * and true tells the recommender that we want to include already known items*/
    	List<RecommendedItem> recommendations = recommender.recommend(userId, model.getNumItems(), null, true);
    	    	   	
    	/*Find the stories that the user have rated*/
    	HashMap<Integer,Integer> ratedStories = conn.getRated((int)userId);

    	ArrayList<Integer> frontendStories = new ArrayList<>();

    	/*Find the stories already present in the recommendations list at front end
    	 * These stories should not be recommended again*/
    	if(add.equals("true")){
    		frontendStories = conn.getStoriesInFrontendArray((int) userId);
    	}
    	int ranking = 1;
    	Random rand = new Random();
    	int randomDislikedRanking = rand.nextInt(6)+5;
    	
    	ArrayList<DatabaseInsertObject> itemsToBeInserted = new ArrayList<>();
    	for (RecommendedItem recommendation : recommendations) {
    		/* To get a story outside of the users preferences, finds the least recommended story */
    		if(randomDislikedRanking == ranking){
    			itemsToBeInserted.add(new DatabaseInsertObject((int)userId, "DF."+recommendations.get(recommendations.size() - 1).getItemID(), "FalseRecommendation", 1, 0, ranking,recommendations.get(recommendations.size() - 1).getValue()));
    			System.out.print("False recommend: ");
    			System.out.println(recommendations.get(recommendations.size() - 1));
    			ranking++;
    			continue;
    		}
    		
    		/*If the item has not been rated and is not already in the recommendation list at front end we insert it*/
    		if ((ratedStories.get((int)recommendation.getItemID())==null) && !frontendStories.contains((int)recommendation.getItemID())){
    			List<RecommendedItem> becauseItems = recommender.recommendedBecause(userId, recommendation.getItemID(), 30);
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
    			String explanation = conn.createExplanation(explanationItems);
    			itemsToBeInserted.add(new DatabaseInsertObject((int)userId, "DF."+recommendation.getItemID(), explanation, 0, 0, ranking, recommendation.getValue()));
    			System.out.println(recommendation); 
        		ranking++;
    		}
    		/*When we got 10 new recommendations, we're happy*/
    		if (ranking > 10){
    			break;
    		}
    	}
    	this.recommendations = recommendations;
    	/*Delete the current recommendations stored in stored_story that has not been seen by the user*/
    	conn.deleteRecommendations((int)userId); 
    	    	
    	/*Insert the 10 items we found*/
    	conn.insertUpdateRecommendValues(itemsToBeInserted);

    	/*Drop our temporary view*/
    	conn.dropView();
    	conn.closeConnection();

    	
    }

    /*Reading the story similarities from file and adding them to a collection of ItemItemSimilarity-objects*/
	private Collection<ItemItemSimilarity> getStorySimilarities() {
		Collection<ItemItemSimilarity> res = new ArrayList<ItemItemSimilarity>();
		BufferedReader br = null;
		try {
			/*This gets the path to the storytelling-backend folder*/
			String url = fileLocation.getParentFile().getParentFile().getParentFile().toString();
			br = new BufferedReader(new FileReader(new File(url+"/personalization/similarities.csv")));
			String line = br.readLine();
			while(line != null){
				String[] values = line.split(",");
				long item1 = Long.parseLong(values[0]);
				long item2 = Long.parseLong(values[1]);
				double value = Double.parseDouble(values[2]);
				res.add(new ItemItemSimilarity(item1, item2, value));
				line = br.readLine();
			}
		} catch (Exception e) {
			e.printStackTrace();
		} finally{
			try {
				br.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
		return res;
	}
	
	public List<RecommendedItem> getRecommendations(){
		return this.recommendations;
	}
}
