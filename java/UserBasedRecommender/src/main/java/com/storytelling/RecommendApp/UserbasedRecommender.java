package com.storytelling.RecommendApp;

import java.io.File;
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


/**
 * Hello world!
 *
 */
public class UserbasedRecommender 
{
    public static void main( String[] args ) throws Exception
    {
		/*This fileDataModel is just for testing. This should be replaced by real data*/
    	DataModel model = new FileDataModel(new File("data/dataset.csv"));
    	
    	/*Comparing the user interactions. This computes the correlation coefficient between user interactions.*/
    	UserSimilarity similarity = new PearsonCorrelationSimilarity(model);
    	/*Deciding for which users to affect the recommender. Here we use all that have a similarity greater than 0.1 */
    	UserNeighborhood neighborhood = new ThresholdUserNeighborhood(0.1, similarity, model);

		/*Recommender*/
    	UserBasedRecommender recommender = new GenericUserBasedRecommender(model, neighborhood, similarity);
    	
    	/*Get 10 recommendations for user 2.*/
    	List<RecommendedItem> recommendations = recommender.recommend(2,10);
    	for (RecommendedItem recommendation : recommendations) {
    	
    	  System.out.println(recommendation);
    	  /*TODO: send data to DB */
    	  
    	}
		
		
	
    }
}
