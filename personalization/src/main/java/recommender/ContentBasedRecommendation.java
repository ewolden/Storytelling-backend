package recommender;

import java.io.File;
import java.io.IOException;
import java.util.Collection;
import java.util.List;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.model.file.FileDataModel;
import org.apache.mahout.cf.taste.impl.similarity.GenericItemSimilarity;
import org.apache.mahout.cf.taste.impl.similarity.GenericItemSimilarity.ItemItemSimilarity;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.apache.mahout.cf.taste.similarity.ItemSimilarity;

/*
 
 My test data looks like this:

user-story triples									|	story-categories matrix
---------------------------------------------------------------------------------------------
(<userId>,<itemId(storyId)>,<preferenceValue>)      | 
(1, 1, 0.8)											|	1	(1,1,0,0,1,0,0,0,0)
(1, 2, 0.0)											|	2	(0,0,1,0,0,0,1,0,0)
(1, 3, 0.3)											|	3	(1,1,0,0,1,0,1,0,0)
(1, 4, 0.2)											|	4	(0,0,1,0,1,1,0,1,1)
(1, 5, 0.4)											|	5	(0,1,0,0,1,1,0,1,1)
(1, 6, 0.2)											|	6	(1,1,0,0,1,0,1,1,0)
(1, 7, 0.0)											|	7	(1,0,1,0,1,1,0,0,0)
(1, 8, 1.0)											|	8	(0,0,0,1,1,1,0,0,0)
(1, 9, 0.0)											|	9   (1,1,0,0,1,1,0,0,0)

 The preferenceValue is a measure on how well a certain story suits the user. Needs to be calculated for every story for a user
 and will be based on the user's category preferences, ratings, number of times recommended and so on, using weights.
 
 This test data produces the following recommendations:

RecommendedItem[item:8, value:0.4215518]
RecommendedItem[item:5, value:0.34887257]
RecommendedItem[item:1, value:0.3478976]
RecommendedItem[item:9, value:0.3466549]
RecommendedItem[item:3, value:0.31633085]
RecommendedItem[item:6, value:0.31077296]
RecommendedItem[item:4, value:0.30006462]
RecommendedItem[item:7, value:0.29899922]
RecommendedItem[item:2, value:0.09940198]

Which looks OK, I suppose. The numbers doesn't say much, the ranking is the important thing. If every preferencesValue is set to 1.0 
all recommendation values will be 1 as well, so it seems like the maximum value is 1.0 and the minimum value is 0. 

TODO: Fetch data about user's preferences from database.
TODO: Compute preferenceValues for all stories for a user by using weighting and other stuff.
TODO: Fetch category mapping for all stories and compute storyCorrelations. May be more accurate to use the subcategories?
TODO: Insert the recommendations in the database. Need to make sure stories already accepted by the user isn't inserted.  
 
 */

public class ContentBasedRecommendation 
{
	
	/*StoryCorrelations are a collection of all story-pairs and their similarity-value
	 * No need to compute this every time a recommendation is made, the categories/subcategories can only change during harvesting*/
	private static Collection<ItemItemSimilarity> storyCorrelations = new StoryCorrelations();
	
    public static void main(String[] args ) throws IOException, TasteException
    {
    	
    	/*Using FileDataModel only for testing, might use the MYSQLJBDCModel when fetching data from database*/
    	DataModel model = new FileDataModel(new File("data/testdata.csv")); 
    	
    	/* CustomGenericItemBasedRecommender need an ItemSimilarity-object as input, so create an instance of this class.
    	 * Don't think it does anything with the computed storyCorrelations*/
    	ItemSimilarity similarity = new GenericItemSimilarity(storyCorrelations);
    	
    	/*Create a new Recommender-instance with our datamodel and storycorrelations*/
    	CustomGenericItemBasedRecommender recommender = new CustomGenericItemBasedRecommender(model, similarity);
    	    	
    	long userId = 1;
    	
    	/* Compute the recommendations. 9 is the number of recommendations we want, don't worry about the null, 
    	 * and true tells the recommender that we want to include already known items*/
    	List<RecommendedItem> recommendations = recommender.recommend(userId, 9, null, true);
    	for (RecommendedItem recommendation : recommendations) {
    	  System.out.println(recommendation);
    	}
    }
}
