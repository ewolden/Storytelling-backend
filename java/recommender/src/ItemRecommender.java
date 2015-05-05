import java.util.ArrayList;
import java.util.List;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.recommender.GenericItemBasedRecommender;
import org.apache.mahout.cf.taste.impl.similarity.LogLikelihoodSimilarity;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.apache.mahout.cf.taste.similarity.ItemSimilarity;

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
 * Creates a list of recommendations using item based collaborative filtering
 * 
 * @author Audun Sæther
 * @author Kjersti Fagerholt 
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

public class ItemRecommender {
	
	/** The user we are making recommendations for */
	private long userId;
	/** The recommender we are using to make the recommendations. A Mahout-object */
	private GenericItemBasedRecommender recommender;
	
	/**
	 * Constructor
	 * 
	 * @param userId	the user we are making recommendations for
	 */
	public ItemRecommender(long userId) {
		this.userId = userId;
	}
	
	/**
	 * Creates a list of recommendations using item based collaborative filtering
	 * 
	 * @param model		a model containing information about users preferences for items. Fetched from "collaborative_view" in the database
	 * @return			the list of recommendations made
	 */
	public ArrayList<CollaborativeRecommendation> RunItemRecommender(DataModel model){

		ArrayList<CollaborativeRecommendation> recommendedItemsList = new ArrayList<CollaborativeRecommendation>();
		try {
			/* Returns the degree of similarity, of two items, based on the preferences that users have expressed for the items. */
			ItemSimilarity sim = new LogLikelihoodSimilarity(model);
			

			/* Given a datamodel and a similarity to produce the recommendations */
			GenericItemBasedRecommender recommender = new GenericItemBasedRecommender(model, sim);
			this.recommender = recommender;
			List<RecommendedItem>recommendations = recommender.recommend(this.userId,167);

			/* Looping through all recommendations and putting up to 167 items in the collaborative recommender list*/
			if(!recommendations.isEmpty()){
				for (RecommendedItem recommendation : recommendations) {
					recommendedItemsList.add(new CollaborativeRecommendation(recommendation, (int)this.userId, "item"));  	  
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
	
	
	/**
	 * Return the recommender that produced the recommendations
	 * 
	 * @return	the recommender
	 */
	public GenericItemBasedRecommender getRecommender(){
		return this.recommender;
	}
}
