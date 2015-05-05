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
 * Provide the information needed to insert recommendations into the stored_story table in the database
 *  
 * @author Audun Sæther
 * @author Kjersti Fagerholt 
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

public class DatabaseInsertObject {
	
	/** The user the recommendations is made for */
	private int userId;
	/** The story that is recommended */
	private String storyId;
	/** The explanation why this story is recommended to this user */
	private String explanation;
	/** Describes if this is a genuine recommendation, or the least recommended story */
	private int false_recommend;
	/** Describe if the recommendation was done by content-based filtering or collaborative filtering */
	private int type_of_recommendation;
	/** The ranking this recommendation has for this user */
	private int ranking;
	/** The value Mahout estimated for this recommendation */
	private double estimatedValue;
	
	/**
	 * Constructor
	 * 
	 * @param userId					the user the recommendations is made for
	 * @param storyId					the story that is recommended
	 * @param explanation				the explanation why this story is recommended to this user
	 * @param false_recommend			describes if this is a genuine recommendation, or the least recommended story
	 * @param type_of_recommendation	describe if the recommendation was done by content-based filtering or collaborative filtering
	 * @param ranking					the ranking this recommendation has for this user
	 * @param estimatedValue			the value Mahout estimated for this recommendation
	 */
	public DatabaseInsertObject(int userId, String storyId, String explanation,
			int false_recommend, int type_of_recommendation, int ranking, double estimatedValue) {
		super();
		this.userId = userId;
		this.storyId = storyId;
		this.explanation = explanation;
		this.false_recommend = false_recommend;
		this.type_of_recommendation = type_of_recommendation;
		this.ranking = ranking;
		this.estimatedValue = estimatedValue;
	}
	
	/**
	 * Returns the userId
	 * @return	the userId
	 */
	public int getUserId() {
		return userId;
	}
	
	/**
	 * Returns the storyId
	 * @return	the storyId
	 */
	public String getStoryId() {
		return storyId;
	}
	
	/**
	 * Returns the explanation
	 * @return	the explanation
	 */
	public String getExplanation() {
		return explanation;
	}
	
	/**
	 * Returns the false_recommend
	 * @return	the false_recommend
	 */
	public int getFalse_recommend() {
		return false_recommend;
	}
	
	/**
	 * Returns the type of recommendation
	 * @return	the type recommendation
	 */
	public int getType_of_recommendation() {
		return type_of_recommendation;
	}
	
	/**
	 * Returns the ranking of this recommendation
	 * @return	the ranking
	 */
	public int getRanking() {
		return ranking;
	}
	
	/**
	 * Returns the estimated value
	 * @return	the estimated value
	 */
	public double getEstimatedValue() {
		return estimatedValue;
	}
	
}
