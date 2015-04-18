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

/*

TODO: Fetch data about user's preferences from database.
TODO: Insert the recommendations in the database. Need to make sure stories already accepted by the user isn't inserted.  
 
 */

public class ContentBasedRecommendation 
{
	File fileLocation = null;
	DatabaseConnection conn = null;
	
    public ContentBasedRecommendation(long userId) throws TasteException, IOException, ClassNotFoundException
    {
    	long startTime = System.nanoTime();
		try {
			fileLocation = new File(this.getClass().getProtectionDomain().getCodeSource().getLocation().toURI());
		} catch (URISyntaxException e) {
			e.printStackTrace();
		}
    	conn = new DatabaseConnection((int) userId);
    	conn.createView((int)userId);
    	conn.setDataModel();
    	/*Using FileDataModel only for testing, might use the MYSQLJBDCModel when fetching data from database*/
    	DataModel model = conn.getDataModel();
    	
    	Collection<ItemItemSimilarity> sim = getStorySimilarities();
    	
    	/* CustomGenericItemBasedRecommender need an ItemSimilarity-object as input, so create an instance of this class.
    	 * Don't think it does anything with the computed storyCorrelations*/
    	ItemSimilarity similarity = new GenericItemSimilarity(sim);
    	
    	/*Create a new Recommender-instance with our datamodel and storycorrelations*/
    	GenericItemBasedRecommender recommender = new GenericItemBasedRecommender(model, similarity);
    	
    	/* Compute the recommendations. 9 is the number of recommendations we want, don't worry about the null, 
    	 * and true tells the recommender that we want to include already known items*/
    	List<RecommendedItem> recommendations = recommender.recommend(userId, 10, null, true);
    	
    	conn.deleteRecommendations((int)userId);
    	int ranking = 1;
    	for (RecommendedItem recommendation : recommendations) {
    		conn.insertUpdateRecommendValues(recommendation, (int) userId, "Mahout",ranking);
    		System.out.println(recommendation); 
    		ranking++;
    	}
    	long elapsedTime = System.nanoTime()-startTime;
    	System.out.println("Mahout time: "+(float)elapsedTime/1000000000);
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
