import java.util.ArrayList;
import java.util.List;

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
 * @author Audun Sæther
 * @author Kjersti Fagerholt 
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

public class MixedFiltering {
	long userId;
	public MixedFiltering(long userId) {
		this.userId = userId;
	}
	
	public void runMixedFiltering(ContentBasedRecommender contentbased, CollaborativeRecommender collaborative) {
		ArrayList<RecommendedItem> mixedRecommendations = new ArrayList<RecommendedItem>();
		
		ArrayList<CollaborativeRecommendation> collaborativeRecommendations = collaborative.getRecommendations();
		List<RecommendedItem> contentbasedRecommendations = contentbased.getRecommendations();
		
		mixedRecommendations.addAll(contentbasedRecommendations);
		System.out.println("--------------");
		
		for(CollaborativeRecommendation recommendation : collaborativeRecommendations){
			//TODO: implement something that does not give duplicates
			
			//mixedRecommendations.add(new CollaborativeRecommendation(recommendation, (int)this.userId, ""));
		}
		
		for(RecommendedItem recommendation : mixedRecommendations){
			System.out.println(recommendation);
			//mixedRecommendations.add(new CollaborativeRecommendation(recommendation, (int)this.userId, ""));
		}
	}
	
}
