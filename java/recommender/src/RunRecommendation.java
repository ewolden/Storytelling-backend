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
 * The class with the main method that is called when the recommender.jar file is run. 
 * Take as input the user we shall compute recommendations for, the method we shall use (content or collaborative) and whether 
 * we shall add recommendations to the stories in the recommendation view at front end or if we should compute a set of new ones
 * 
 * @author Audun Sæther
 * @author Kjersti Fagerholt 
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

public class RunRecommendation {
	
	/**
	 * This method is run when the recommender.jar file is run.
	 * Starts the recommendation classes based on the given input.
	 * 
	 * @param args	a String array that contains the userId, the method to be used, and a string declaring whether to add recommendations or not
	 */
	public static void main(String[] args) {
		long startTime = System.nanoTime();
		long userId = Long.parseLong(args[0]);
		System.out.println("UserId: "+userId);
		String method = args[1];
		System.out.println("Method: " + method);
		String add = args[2];
		System.out.println("Add?: " + add);
		
		if(method.equals("content")){
			try {
				ContentBasedRecommender cbr = new ContentBasedRecommender(userId, add);
				cbr.runContentBasedRecommender();
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
		else if(method.equals("collaborative")){
			int numberOfStoriesRecommended = 0;
			try {
				CollaborativeRecommender collaborativeRecommender = new CollaborativeRecommender(userId, add);
				numberOfStoriesRecommended = collaborativeRecommender.runCollaborativeRecommender();
				
//				if(numberOfStoriesRecommended < 10 && false){ //disabled
//					/* There are not enough collaborative recommendations, need content based in addition */
//					ContentBasedRecommender cbr = new ContentBasedRecommender(userId, add);
//					cbr.runContentBasedRecommender();
//					MixedFiltering mixed = new MixedFiltering(userId);
//					mixed.runMixedFiltering(cbr, collaborativeRecommender);
//				}
				
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
		else if(method.equals("hybrid")){
			System.out.println("Hybrid recommending not yet implementing");
		}
		else {
			System.out.println("Wrong method");
		}

		long elapsedTime = System.nanoTime()-startTime;
    	System.out.println("Mahout time: "+(float)elapsedTime/1000000000);
	}

}
