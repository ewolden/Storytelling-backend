import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.net.URISyntaxException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.recommender.GenericItemBasedRecommender;
import org.apache.mahout.cf.taste.impl.similarity.GenericItemSimilarity;
import org.apache.mahout.cf.taste.impl.similarity.GenericItemSimilarity.ItemItemSimilarity;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.apache.mahout.cf.taste.similarity.ItemSimilarity;


public class ContentBasedRecommender
{
	File fileLocation = null;
	DatabaseConnection conn = null;
	long userId;
	
    public ContentBasedRecommender(long userId) {
    	this.userId = userId;
    }
    
    public void runContentBasedRecommender() throws TasteException{
		try {
			fileLocation = new File(this.getClass().getProtectionDomain().getCodeSource().getLocation().toURI());
		} catch (URISyntaxException e) {
			e.printStackTrace();
		}
		/*"content"+userId is the name of the view shall create*/

		conn = new DatabaseConnection("content"+userId);
    	/*Create a view the includes all preferences values for this user*/
    	conn.createView((int)userId);

    	conn.setDataModel();

    	DataModel model = conn.getDataModel();
    	
    	Collection<ItemItemSimilarity> sim = getStorySimilarities();
    	/* CustomGenericItemBasedRecommender need an ItemSimilarity-object as input, so create an instance of this class.*/
    	ItemSimilarity similarity = new GenericItemSimilarity(sim);
    	
    	/*Create a new Recommender-instance with our datamodel and story similarities*/
    	GenericItemBasedRecommender recommender = new GenericItemBasedRecommender(model, similarity);
    	
    	/* Compute the recommendations. 167 is the number of recommendations we want, don't worry about the null, 
    	 * and true tells the recommender that we want to include already known items*/
    	List<RecommendedItem> recommendations = recommender.recommend(userId, 167, null, true);
    	    	
    	/*Delete the current recommendations stored in stored_story*/
    	conn.deleteRecommendations((int)userId); 
    	
    	/*Find the stories that the user have read or rejected*/
    	ArrayList<Integer> readOrRejected = conn.getRated((int)userId);

    	ArrayList<DatabaseInsertObject> itemsToBeInserted = new ArrayList<>();
    	int ranking = 1;
    	for (RecommendedItem recommendation : recommendations) {
    		/*If the item has not been read or rejected we insert it*/
    		if (!readOrRejected.contains((int)recommendation.getItemID())){
    			itemsToBeInserted.add(new DatabaseInsertObject((int)userId, "DF."+recommendation.getItemID(), "mahout", 0, 0, ranking));
        		System.out.println(recommendation); 
        		ranking++;
    		}
    		/*When we got 10 new recommendations, we're happy*/
    		if (ranking > 10){
    			break;
    		}
    	}
    	conn.insertUpdateRecommendValues(itemsToBeInserted);

    	conn.dropView();
    	conn.closeConnection();

    	
    }

    /*Reading the story similarities from file and adding them to a collection of ItemItemSimilarity-objects*/
	private Collection<ItemItemSimilarity> getStorySimilarities() {
		Collection<ItemItemSimilarity> res = new ArrayList<ItemItemSimilarity>();
		BufferedReader br = null;
		try {
			/*This gets the path to the storytelling-backend folder*/
			String url = fileLocation.getParentFile().getParentFile().getParentFile().toString();
			br = new BufferedReader(new FileReader(new File(url+"/personalization/similarities.csv")));
			String line = br.readLine();
			while(line != null){
				String[] values = line.split(",");
				long item1 = Long.parseLong(values[0]);
				long item2 = Long.parseLong(values[1]);
				double value = Double.parseDouble(values[2]);
				res.add(new ItemItemSimilarity(item1, item2, value));
				line = br.readLine();
			}
		} catch (Exception e) {
			e.printStackTrace();
		} finally{
			try {
				br.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
		return res;
	}
}
