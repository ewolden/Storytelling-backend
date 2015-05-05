import java.util.ArrayList;
import java.util.List;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.neighborhood.ThresholdUserNeighborhood;
import org.apache.mahout.cf.taste.impl.recommender.GenericUserBasedRecommender;
import org.apache.mahout.cf.taste.impl.similarity.PearsonCorrelationSimilarity;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.neighborhood.UserNeighborhood;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.apache.mahout.cf.taste.recommender.UserBasedRecommender;
import org.apache.mahout.cf.taste.similarity.UserSimilarity;

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
 * Creates a list of recommendations using user based collaborative filtering
 * 
 * @author Audun Sæther
 * @author Kjersti Fagerholt 
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

public class UserbasedRecommender {	
	
	/** The user we shall compute recommendations for */
	private long userId;
	
	/**
	 * Constructor
	 * 
	 * @param userId	the user we shall compute recommendations for
	 */
	public UserbasedRecommender(long userId){	
		this.userId = userId;	
	}

	/**
	 * Method that creates a list of recommendations based on collaborative filtering
	 * 
	 * @param model		the data needed. The data is what is stored at the moment in the collaborativ_view in the database.
	 * @return			the list of computed recommendations
	 */
	public ArrayList<CollaborativeRecommendation> RunUserbasedRecommender(DataModel model){

		ArrayList<CollaborativeRecommendation> recommendedItemsList = new ArrayList<CollaborativeRecommendation>();
		try {
			/*Comparing the user interactions. This computes the correlation coefficient between user interactions.*/
			UserSimilarity similarity = new PearsonCorrelationSimilarity(model);

			/*Deciding for which users to affect the recommender. Here we use all that have a similarity greater than 0.1 */
			UserNeighborhood neighborhood = new ThresholdUserNeighborhood(0.1, similarity, model);

			/*Recommender*/
			UserBasedRecommender recommender = new GenericUserBasedRecommender(model, neighborhood, similarity);

			/*Get recommendations for this userId*/
			List<RecommendedItem> recommendations = recommender.recommend(userId,167);
			if(!recommendations.isEmpty()){
				for (RecommendedItem recommendation : recommendations) {
					recommendedItemsList.add(new CollaborativeRecommendation(recommendation, (int)userId, "user based"));  	  
				}
			}else{
				/*There are no recommendations for this user*/
				System.out.println("No recommendations for this user in userbased");
			}
		
		} catch (TasteException e) {
			e.printStackTrace();
		}
		return recommendedItemsList;
	}
}
