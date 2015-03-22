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
(1, 15, 0.8)										|	15	(1,1,0,0,1,0,0,0,0)
(1, 13, 0.0)										|	13	(0,0,1,0,0,0,1,0,0)
(1, 12, 0.3)										|	12	(1,1,0,0,1,0,1,0,0)
(1, 11, 0.2)										|	11	(0,0,1,0,1,1,0,1,1)
(1, 14, 0.4)										|	14	(0,1,0,0,1,1,0,1,1)
(1, 43, 0.2)										|	43	(1,1,0,0,1,0,1,1,0)
(1, 24, 0.0)										|	24	(1,0,1,0,1,1,0,0,0)
(1, 65, 1.0)										|	65	(0,0,0,1,1,1,0,0,0)
(1, 34, 0.0)										|	34  (1,1,0,0,1,1,0,0,0)

 The preferenceValue is a measure on how well a certain story suits the user. Needs to be calculated for every story for a user
 and will be based on the user's category preferences, ratings, number of times recommended and so on, using weights.
 
 This test data produces the following recommendations with different similarity measures:

Cosine similarity:								|	Euclidean distance:
												|
RecommendedItem[item:65, value:0.4215518]		|	RecommendedItem[item:65, value:0.4443294]
RecommendedItem[item:14, value:0.34887257]		|	RecommendedItem[item:15, value:0.38496244]
RecommendedItem[item:15, value:0.3478976]		|	RecommendedItem[item:14, value:0.33824167]
RecommendedItem[item:34, value:0.3466549]		|	RecommendedItem[item:12, value:0.32404113]
RecommendedItem[item:12, value:0.31633085]		|	RecommendedItem[item:43, value:0.3050273]
RecommendedItem[item:43, value:0.31077296]		|	RecommendedItem[item:11, value:0.2987439]
RecommendedItem[item:11, value:0.30006462]		|	RecommendedItem[item:34, value:0.29107738]
RecommendedItem[item:24, value:0.29899922]		|	RecommendedItem[item:24, value:0.26796514]
RecommendedItem[item:13, value:0.09940198]		|	RecommendedItem[item:13, value:0.25658306]


Jaccard coefficient:

RecommendedItem[item:65, value:0.48336]
RecommendedItem[item:15, value:0.3608324]
RecommendedItem[item:14, value:0.34037268]
RecommendedItem[item:34, value:0.33158708]
RecommendedItem[item:12, value:0.32059234]
RecommendedItem[item:43, value:0.30770108]
RecommendedItem[item:11, value:0.28867924]
RecommendedItem[item:24, value:0.2725327]
RecommendedItem[item:13, value:0.073076926]


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
