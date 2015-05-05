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
 * Represents a recommendation created by collaborative filtering.
 * 
 * @author Audun Sæther
 * @author Kjersti Fagerholt 
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

public class CollaborativeRecommendation {
	/**The recommendation for this object*/
	private RecommendedItem item;
	/**The user the recommendation was made for*/
	private long userId;
	/**Explanation for why this recommendation was made*/
	private String explanation;
	
	/**
	 * Constructor
	 * 
	 * @param item	 		an RecommendedItem produced by Mahout
	 * @param userId 		a number representing the user for which the recommendations is made
	 * @param explanation	a string consisting of an explanation of why the item was recommended to this user
	 */	
	public CollaborativeRecommendation(RecommendedItem item, long userId, String explanation){
		this.item = item;
		this.userId = userId;
		this.explanation = explanation;
	}
	

	/** 
	 * Method for getting the recommendation item
	 * 
	 * @return item			the item for the given CollaborativeRecommendation-object
	 */
	public RecommendedItem getItem() {
		return item;
	}

	/**
	 * Method for setting the recommendation item
	 * 
	 * @param item			the item to be set
	 */
	public void setItem(RecommendedItem item) {
		this.item = item;
	}

	/**
	 * Method for getting the userId for this object
	 * 
	 * @return				the userId for this object
	 */
	public long getUserId() {
		return userId;
	}

	/**
	 * Method for setting the userId for this object
	 * 
	 * @param userId		the userId to be set
	 */
	public void setUserId(int userId) {
		this.userId = userId;
	}

	/**
	 * Method for getting the explanation for this object
	 * 
	 * @return				the explanation for this object
	 */
	public String getExplanation() {
		return explanation;
	}

	/**
	 * Method for setting the explanation for this object
	 * 
	 * @param explanation	the explanation to be set
	 */
	public void setExplanation(String explanation) {
		this.explanation = explanation;
	}
}
