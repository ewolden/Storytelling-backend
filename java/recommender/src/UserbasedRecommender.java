

import java.io.File;
import java.util.ArrayList;
import java.util.List;

import org.apache.mahout.cf.taste.impl.model.file.FileDataModel;
import org.apache.mahout.cf.taste.impl.neighborhood.ThresholdUserNeighborhood;
import org.apache.mahout.cf.taste.impl.recommender.GenericUserBasedRecommender;
import org.apache.mahout.cf.taste.impl.similarity.PearsonCorrelationSimilarity;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.neighborhood.UserNeighborhood;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.apache.mahout.cf.taste.recommender.UserBasedRecommender;
import org.apache.mahout.cf.taste.similarity.UserSimilarity;

public class UserbasedRecommender 
{	private long userId;
    public UserbasedRecommender(long userId) 
    {	this.userId = userId;	}
    
    public ArrayList<CollaborativeRecommendation> RunUserbasedRecommender() throws Exception{
    	
    	ArrayList<CollaborativeRecommendation> recommendedItemsList = new ArrayList<CollaborativeRecommendation>();
    	
    	
    	DatabaseConnection conn = new DatabaseConnection("collaborative_view");
    	conn.setDataModel();
    	DataModel model = conn.getDataModel();
		/*Testing*
    	//DataModel model = new FileDataModel(new File("data/dataset.csv"));
    	
    	/*Comparing the user interactions. This computes the correlation coefficient between user interactions.*/
    	UserSimilarity similarity = new PearsonCorrelationSimilarity(model);

    	/*Deciding for which users to affect the recommender. Here we use all that have a similarity greater than 0.1 */
    	UserNeighborhood neighborhood = new ThresholdUserNeighborhood(0.0000001, similarity, model);
    	
		/*Recommender*/
    	UserBasedRecommender recommender = new GenericUserBasedRecommender(model, neighborhood, similarity);
    	
    	/*Get 10 recommendations for this userId*/
    	List<RecommendedItem> recommendations = recommender.recommend(userId,10, null, true);
    	if(!recommendations.isEmpty()){
    		int ranking = 0;
    		ArrayList<DatabaseInsertObject> itemsToBeInserted = new ArrayList<>();
    		for (RecommendedItem recommendation : recommendations) {
    			itemsToBeInserted.add(new DatabaseInsertObject((int)userId, "DF."+recommendation.getItemID(), "test", 0, 0, ranking));
				recommendedItemsList.add(new CollaborativeRecommendation(recommendation, (int)userId, "explanationTestUSER"));
    	    	ranking++;    	  
    	    }
    		conn.insertUpdateRecommendValues(itemsToBeInserted);
    	 }else{
    		/*There are no recommendations for this user*/
    		System.out.println("No recommendations for this user");
    	}
		return recommendedItemsList;
	
    }
}
