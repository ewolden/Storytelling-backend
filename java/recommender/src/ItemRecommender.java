

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
	public ArrayList<CollaborativeRecommendation> RunItemRecommender(DataModel model){

		ArrayList<CollaborativeRecommendation> recommendedItemsList = new ArrayList<CollaborativeRecommendation>();
		try {
			/* Returns the degree of similarity, of two items, based on the preferences that users have expressed for the items. */
			ItemSimilarity sim = new LogLikelihoodSimilarity(model);
			

			/* Given a datamodel and a similarity to produce the recommendations */
			GenericItemBasedRecommender recommender = new GenericItemBasedRecommender(model, sim);
			List<RecommendedItem>recommendations = recommender.recommend(this.userId,167);

			/* Looping through all recommendations and putting up to 167 items in the collaborative recommender list*/
			if(!recommendations.isEmpty()){
				for (RecommendedItem recommendation : recommendations) {
					recommendedItemsList.add(new CollaborativeRecommendation(recommendation, (int)this.userId, "item based"));  	  
				}
			}else{
				/*There are no recommendations for this user*/
				System.out.println("No recommendations for this user in itembased");
			}	

		} catch (TasteException e) {
			e.printStackTrace();
		}
		return recommendedItemsList;
	}
}
