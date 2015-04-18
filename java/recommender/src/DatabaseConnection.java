
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;

import org.apache.mahout.cf.taste.common.Refreshable;
import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.model.jdbc.MySQLJDBCDataModel;
import org.apache.mahout.cf.taste.impl.model.jdbc.ReloadFromJDBCDataModel;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.model.JDBCDataModel;

import com.mysql.jdbc.jdbc2.optional.MysqlConnectionPoolDataSource;

public class DatabaseConnection {
	Connection connection;
	DataModel model;
	ReloadFromJDBCDataModel reloadModel;
	MysqlConnectionPoolDataSource dataSource;
	
	/*Will be content+userId when doing content-based filtering and collaborative_view when doing collaborative filtering*/
	private String viewName;

	/**Creates a connection to the database*/
	public DatabaseConnection(String viewName) throws TasteException{
		this.viewName = viewName;
		
		dataSource = new MysqlConnectionPoolDataSource();

		dataSource.setServerName(Globals.DB_HOST);
		dataSource.setUser(Globals.DB_USERNAME);
		//dataSource.setPotNumber(3306);
		dataSource.setPassword(Globals.DB_PASSWORD);
		dataSource.setDatabaseName(Globals.DB_NAME);
		try {
			dataSource.setCachePreparedStatements(true);
			dataSource.setCachePrepStmts(true);
			dataSource.setCacheResultSetMetadata(true);
			dataSource.setAlwaysSendSetIsolation(false);
			dataSource.setElideSetAutoCommits(true);
			connection = dataSource.getConnection();
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
	
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
	 * @return model	Model containing information from database
	 */
	public DataModel getDataModel(){
		return model;
	}
	
	/**Refreshes ReloadFromJDBCDataModel in memory*/
	public void refresh(Collection<Refreshable> alreadyRefreshed){
		reloadModel.refresh(alreadyRefreshed);
	}
	
	/**Add recommendations to database*/
	public void insertUpdateRecommendValues(ArrayList<DatabaseInsertObject> listOfRecommendations){
		try {
			String insertUpdateSql = "INSERT INTO stored_story (userId, storyId, explanation, false_recommend,type_of_recommendation,recommend_ranking)"
					+ "VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE "
					+ "explanation = ?, false_recommend = ?, type_of_recommendation=?, recommend_ranking = ?";
			PreparedStatement stmt = connection.prepareStatement(insertUpdateSql);
			
			for (DatabaseInsertObject item: listOfRecommendations){
				stmt.setInt(1, item.getUserId());
				stmt.setString(2, item.getStoryId());
				stmt.setString(3, item.getExplanation());
				stmt.setInt(4, item.getFalse_recommend());
				stmt.setInt(5, item.getType_of_recommendation());
				stmt.setInt(6, item.getRanking());
				stmt.setString(7, item.getExplanation());
				stmt.setInt(8, item.getFalse_recommend());
				stmt.setInt(9, item.getType_of_recommendation());
				stmt.setInt(10, item.getRanking());
				stmt.addBatch();		
			}
			stmt.executeBatch();
			stmt.close();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}
	
	public void deleteRecommendations(int userId){
		try {
			PreparedStatement stmt = connection.prepareStatement("DELETE FROM stored_story where userId=? and recommend_ranking IS NOT NULL");
			stmt.setInt(1, userId);
			stmt.executeUpdate();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}
	
	public void createView(int userId){
		try {
			PreparedStatement stmt = connection.prepareStatement(
					"CREATE VIEW "+viewName+" as SELECT * FROM preference_value WHERE userId=?");
			stmt.setInt(1, userId);
			stmt.execute();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		
	}
	
	public void dropView(){
		try {
			PreparedStatement stmt = connection.prepareStatement(
					"DROP VIEW "+viewName);
			stmt.execute();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}
	
	/**Close connection to database*/
	public void closeConnection(){
		try {
			connection.close();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}
	
}
