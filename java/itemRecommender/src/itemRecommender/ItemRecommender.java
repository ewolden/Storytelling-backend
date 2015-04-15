package itemRecommender;

import java.io.File;
import java.io.IOException;
import java.util.List;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.common.LongPrimitiveIterator;
import org.apache.mahout.cf.taste.impl.model.file.FileDataModel;
import org.apache.mahout.cf.taste.impl.recommender.GenericItemBasedRecommender;
import org.apache.mahout.cf.taste.impl.similarity.LogLikelihoodSimilarity;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.apache.mahout.cf.taste.similarity.ItemSimilarity;

public class ItemRecommender {

	public static void main(DataModel datamodel) {
		try {
			/*This fileDataModel is just for testing. This should be replaced by real data*/
			DataModel dm = new FileDataModel(new File("data/dataset.csv"));
			
			/* Returns the degree of similarity, of two items, based on the preferences that users have expressed for the items.
			Should take in "datamodel" as an argument, not det test dataset "dm" */
			ItemSimilarity sim = new LogLikelihoodSimilarity(dm);
			
			/* Given a datamodel and a similarity to produce the recommendations. Should take datamodel as an argument, not dm */
			GenericItemBasedRecommender recommender = new GenericItemBasedRecommender(dm, sim);
			
			/*Looping through all the itemIds and will put 5 recommendations for every item in the rcommendations list.  */
			
			for(LongPrimitiveIterator items = dm.getItemIDs(); items.hasNext();){
				long itemId = items.nextLong();
				List<RecommendedItem>recommendations = recommender.mostSimilarItems(itemId,5);
				/*Looping through all the recommendations for this item. */
				/*recommendation.getValue() Shows how similar two items are.*/
				for(RecommendedItem recommendation : recommendations){
					System.out.println(itemId+ "," + recommendation.getItemID() + "," + recommendation.getValue());
					
					/*TODO: Send item recommendations to DB. */
				}
				
			}	
			
			
		} catch (IOException e) {
			
			e.printStackTrace();
		} catch (TasteException e) {
			e.printStackTrace();
		}
		

	}

}
