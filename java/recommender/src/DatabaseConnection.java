
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;

import org.apache.mahout.cf.taste.common.Refreshable;
import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.model.jdbc.MySQLJDBCDataModel;
import org.apache.mahout.cf.taste.impl.model.jdbc.ReloadFromJDBCDataModel;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.model.JDBCDataModel;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;

import com.mysql.jdbc.jdbc2.optional.MysqlConnectionPoolDataSource;

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
 * Provides a connection to the database and methods to get and insert data into the database.
 * 
 * @author Audun Sæther
 * @author Kjersti Fagerholt 
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

public class DatabaseConnection {
	/** Connection-object to the database */
	Connection connection;
	/** Holds the information about users and the preferences for items(stories) */
	DataModel model;
	/** A DataModel that can reload data into memory */
	ReloadFromJDBCDataModel reloadModel;
	/** Used for our MySQL JDBC connection */
	MysqlConnectionPoolDataSource dataSource;
	/** Will be "content"+userId when doing content-based filtering and "collaborative_view" when doing collaborative filtering*/
	private String viewName;

	/**
	 * Constructor
	 * 
	 * @param viewName	the name of the view from which the user(s) preferences should be fetched	
	 */
	public DatabaseConnection(String viewName){
		this.viewName = viewName;
		dataSource = new MysqlConnectionPoolDataSource();
	}
	
	/**
	 * Creates the connection to the database
	 */
	public void setConnection(){
		dataSource.setServerName(Globals.DB_HOST);
		dataSource.setUser(Globals.DB_USERNAME);
		//dataSource.setPotNumber(3306);
		dataSource.setPassword(Globals.DB_PASSWORD);
		//This if-statement is to allow setting the database to "testingDatabase" below without overwriting it here
		if (dataSource.getDatabaseName().isEmpty()){
			dataSource.setDatabaseName(Globals.DB_NAME);			
		}
		try {
			dataSource.setCachePreparedStatements(true);
			dataSource.setCachePrepStmts(true);
			dataSource.setCacheResultSetMetadata(true);
			dataSource.setAlwaysSendSetIsolation(false);
			dataSource.setElideSetAutoCommits(true);
			dataSource.setRewriteBatchedStatements(true);
			connection = dataSource.getConnection();
			connection.setAutoCommit(false);
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
	
	/**
	 * Sets the dataModel for this connection
	 */
	public void setDataModel(){	
			JDBCDataModel dataModel = new MySQLJDBCDataModel(
					dataSource, viewName, "userId",
					"numericalId", "preferenceValue", "time_stamp");

			try {
				reloadModel = new ReloadFromJDBCDataModel(dataModel);
			} catch (TasteException e) {
				e.printStackTrace();
			}
			if(reloadModel != null){
				model = reloadModel.getDelegateInMemory();

			} else model = dataModel;
		
	}

	/**
	 * Returns model with database information
	 * 
	 * @return model	model containing information from database
	 */
	public DataModel getDataModel(){
		return model;
	}
	
	/**
	 * Refreshes ReloadFromJDBCDataModel in memory
	 * 
	 * @param alreadyRefreshed
	 */
	public void refresh(Collection<Refreshable> alreadyRefreshed){
		reloadModel.refresh(alreadyRefreshed);
	}
	
	/**
	 * Add recommendations to the database
	 * 
	 * @param listOfRecommendations		list of DatabaseInsertObjects to be inserted in the database
	 */
	public void insertUpdateRecommendValues(ArrayList<DatabaseInsertObject> listOfRecommendations){
		try {
			/*SQL-statement for inserted new values or updating existing ones if the story already exists in the stored_stoy table for this user*/
			String insertUpdateSql = "INSERT INTO stored_story (userId, storyId, explanation, false_recommend,type_of_recommendation,recommend_ranking,estimated_Rating)"
					+ "VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE "
					+ "explanation = ?, false_recommend = ?, type_of_recommendation=?, recommend_ranking = ?, estimated_Rating = ?";
			PreparedStatement stmt = connection.prepareStatement(insertUpdateSql);
			
			/*Looping through all the recommendations and make them ready for insert*/
			for (DatabaseInsertObject item: listOfRecommendations){
				stmt.setInt(1, item.getUserId());
				stmt.setString(2, item.getStoryId());
				stmt.setString(3, item.getExplanation());
				stmt.setInt(4, item.getFalse_recommend());
				stmt.setInt(5, item.getType_of_recommendation());
				stmt.setInt(6, item.getRanking());
				stmt.setDouble(7, item.getEstimatedValue());
				stmt.setString(8, item.getExplanation());
				stmt.setInt(9, item.getFalse_recommend());
				stmt.setInt(10, item.getType_of_recommendation());
				stmt.setInt(11, item.getRanking());
				stmt.setDouble(12, item.getEstimatedValue());
				stmt.addBatch();		
			}
			/*Insert the recommendations*/
			stmt.executeBatch();
			/*Not sure what this does, but its supposed to make it faster (combined with connection.setAutoCommit(false) above)*/
			connection.commit();
			stmt.close();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}
	
	/**
	 * Delete the recommendations in the stored_story that the user have not seen (that is, stories that user has not seen at any point, not just for this recommendation list)
	 * 
	 * @param userId	the user for which we shall delete the recommendations
	 */
	public void deleteRecommendations(int userId){
		try {
			emptyRecommendationsRankings(userId);
			
			/*Find the stories in stored_story where the recommended-state has not been recorded and that are not in the front end array*/
			PreparedStatement stmt = connection.prepareStatement(
					"SELECT so.storyId FROM stored_story AS so "
					+ "LEFT JOIN story_state AS sa ON so.storyId=sa.storyId AND so.userId=sa.userId "
					+ "WHERE so.userId=? AND sa.stateId IS NULL AND so.in_frontend_array = 0");
			stmt.setInt(1, userId);
			ResultSet rs = stmt.executeQuery();
			
			/*Delete the stories we found above*/
			stmt = connection.prepareStatement(
					"DELETE FROM stored_story WHERE userId=? AND storyId=?");
			while (rs.next()){
				stmt.setInt(1, userId);
				stmt.setString(2, rs.getString("so.storyId"));
				stmt.addBatch();
			}
			stmt.executeBatch();
			connection.commit();
			stmt.close();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}
	
	/**
	 * Remove the current rankings in stored_story for this user
	 * 
	 * @param userId	the user we shall remove the rankings for
	 */
	public void emptyRecommendationsRankings(int userId){
		try {
			PreparedStatement stmt = connection.prepareStatement(
					"UPDATE stored_story SET recommend_ranking=null WHERE userId=? and recommend_ranking IS NOT NULL");
			stmt.setInt(1, userId);
			stmt.executeUpdate();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}
	
	/**
	 * Find the list of rated stories for this user and return it
	 * 
	 * @param userId	the user we shall find the rated stories for
	 * @return			a HashMap of the stories with their rankings
	 */
	public HashMap<Integer, Integer> getRated(int userId){
		HashMap<Integer, Integer> ratedStories = new HashMap<>();
		
		try {
			PreparedStatement stmt = connection.prepareStatement(
					"SELECT storyId, rating FROM stored_story WHERE userId=? AND rating IS NOT NULL");
			stmt.setInt(1,userId);
			ResultSet rs = stmt.executeQuery();
			while (rs.next()){
				String id = rs.getString("storyId");
				int numId = Integer.parseInt(id.substring(3));
				ratedStories.put(numId,rs.getInt("rating"));
			}
		} catch (SQLException e) {
			e.printStackTrace();
		}		
		return ratedStories;
	}
	
	/**
	 * Finds the stories currently showed to the user in the recommendation view
	 * 
	 * @param userId	the user we shall find the stories for
	 * @return			a list of the numerical IDs for the stories we found
	 */
	public ArrayList<Integer> getStoriesInFrontendArray(int userId) {
		ArrayList<Integer> frontendStories = new ArrayList<>();
		
		try {
			PreparedStatement stmt = connection.prepareStatement(
					"SELECT storyId FROM stored_story WHERE userId=? AND in_frontend_array=?");
			stmt.setInt(1, userId);
			stmt.setInt(2, 1);
			ResultSet rs = stmt.executeQuery();
			while(rs.next()){
				String id = rs.getString("storyId");
				int numId = Integer.parseInt(id.substring(3));
				frontendStories.add(numId);
			}
		} catch (SQLException e) {
			e.printStackTrace();
		}
		
		return frontendStories;		
	}
	
	/**
	 * Gets the title of the explanation stories and creates an explanation string.
	 * The string consists of storyId:title-pair, each pair separated by commas
	 * 
	 * @param explanationItems	a list of the items that is the explanation of the recommendation
	 * @return					a string with the story-IDs and their title.
	 */
	public String createExplanation(ArrayList<RecommendedItem> explanationItems) {
		String explanation = "";
		try {
			PreparedStatement stmt = connection.prepareStatement(
					"SELECT title FROM story WHERE storyId=?");
			for(RecommendedItem item: explanationItems){
				stmt.setString(1, "DF."+item.getItemID());
				ResultSet rs = stmt.executeQuery();
				while(rs.next()){
					explanation += "DF."+item.getItemID()+":"+rs.getString("title")+",";					
				}
			}
			stmt.close();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		/*Remove the last comma*/
		explanation = explanation.replaceAll(",$", "");
		return explanation;
	}
	
	/**
	 * Create a view in the database with the preference values for the input user
	 * 
	 * @param userId	the user we create the view for
	 */
	public void createView(int userId){
		try {
			PreparedStatement stmt = connection.prepareStatement(
					"CREATE or REPLACE VIEW "+viewName+" as SELECT * FROM preference_value WHERE userId=?");
			stmt.setInt(1, userId);
			stmt.execute();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		
	}
	
	/**
	 * Drop the view created above
	 */
	public void dropView(){
		try {
			PreparedStatement stmt = connection.prepareStatement(
					"DROP VIEW IF EXISTS "+viewName);
			stmt.execute();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}
	
	/**
	 * Close the connection to the database
	 */
	public void closeConnection(){
		try {
			connection.close();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}

	/**
	 * Method just for testing purposes, need to set the database as testingDatabase when testing
	 * 
	 * @param name		the name of the database we want to connect to
	 */
	public void setDatabaseName(String name){
		dataSource.setDatabaseName(name);
	}

	
}
