

import java.util.ArrayList;
import java.util.List;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.common.LongPrimitiveIterator;
import org.apache.mahout.cf.taste.impl.recommender.GenericItemBasedRecommender;
import org.apache.mahout.cf.taste.impl.similarity.LogLikelihoodSimilarity;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.apache.mahout.cf.taste.similarity.ItemSimilarity;


public class ItemRecommender {
	private long userId;
	public ItemRecommender(long userId) {
		this.userId = userId;
	}
	public ArrayList<CollaborativeRecommendation> RunItemRecommender(){
		
		ArrayList<CollaborativeRecommendation> recommendedItemsList = new ArrayList<CollaborativeRecommendation>();
		try {
			/*This fileDataModel is just for testing. This should be replaced by real data*/
			//DataModel dm = new FileDataModel(new File("data/dataset.csv"));
	    	DatabaseConnection conn = new DatabaseConnection("collaborative_view");
	    	conn.setDataModel();
	    	DataModel dm = conn.getDataModel();
	    	
			/* Returns the degree of similarity, of two items, based on the preferences that users have expressed for the items. */
			ItemSimilarity sim = new LogLikelihoodSimilarity(dm);
			
			/* Given a datamodel and a similarity to produce the recommendations */
			GenericItemBasedRecommender recommender = new GenericItemBasedRecommender(dm, sim);
			
			/*Looping through all the itemIds and will put 5 recommendations for every item in the recommendations list.  */
			for(LongPrimitiveIterator items = dm.getItemIDs(); items.hasNext();){
				long itemId = items.nextLong();
				List<RecommendedItem>recommendations = recommender.mostSimilarItems(itemId,5);
				/*Looping through all the recommendations for this item. */
				/*recommendation.getValue() Shows how similar two items are.*/
				
				if(!recommendations.isEmpty()){
					for(RecommendedItem recommendation : recommendations){
						/*Filter the recommendations, only save the ones that have the recommendation.getValue() greater than 0.5*/
						if(recommendation.getValue() > 0.01){
							//conn.insertUpdateRecommendValues(recommendation, (int)userId, "kjerstiiiiiitestItem");
							//System.out.println(itemId+ "," + recommendation.getItemID() + "," + recommendation.getValue());
							recommendedItemsList.add(new CollaborativeRecommendation(recommendation, (int)userId, "ExplanationTestITEM"));
						}else{
							System.out.println("Recommendations were made, but no one that where higher than 0.5");
						}
					}
				}else{
					/*There are no recommendations for this user*/
					//System.out.println("There are no recommendations for this user");
				}	
			}	
			
			
		} catch (TasteException e) {
			e.printStackTrace();
		}
		return recommendedItemsList;
		

	}

}
