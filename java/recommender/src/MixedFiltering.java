import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

import org.apache.mahout.cf.taste.recommender.RecommendedItem;


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
