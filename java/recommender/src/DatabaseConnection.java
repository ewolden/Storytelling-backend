import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.Collection;

import org.apache.mahout.cf.taste.common.Refreshable;
import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.model.jdbc.MySQLJDBCDataModel;
import org.apache.mahout.cf.taste.impl.model.jdbc.ReloadFromJDBCDataModel;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.model.JDBCDataModel;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;

import com.mysql.jdbc.jdbc2.optional.MysqlConnectionPoolDataSource;


public class DatabaseConnection {
	Connection connection;
	DataModel model;
	ReloadFromJDBCDataModel reloadModel;

	/**Creates a connection to the database*/
	public DatabaseConnection() throws TasteException{
		MysqlConnectionPoolDataSource dataSource = new MysqlConnectionPoolDataSource();

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

			JDBCDataModel dataModel = new MySQLJDBCDataModel(
					dataSource, "preference_value", "userId",
					"numericalId", "preferenceValue", "time_stamp");


			reloadModel = new ReloadFromJDBCDataModel(dataModel);
			if(reloadModel != null){
				model = reloadModel.getDelegateInMemory();

			} else model = dataModel;
		} catch (Exception e) {
			e.printStackTrace();
		}
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
	public void insertUpdateRecommendValues(RecommendedItem item, int userId, String explanation, int ranking){
		try {
			PreparedStatement stmt = connection.prepareStatement("UPDATE stored_story "
					+ "SET explanation = ?, recommend_ranking = ? " // + " AND " ETTELLERANNET ANNET SKAL HER
					+ "WHERE userId = ? AND storyId = ?");
			stmt.setString(1, explanation);
			stmt.setInt(2, ranking);
			stmt.setInt(3, userId);
			stmt.setString(4, "DF."+item.getItemID());
			int result = stmt.executeUpdate();
			if(result == 0){
				stmt = connection.prepareStatement("INSERT INTO stored_story (userId, storyId, explanation, false_recommend,type_of_recommendation,recommend_ranking) " //OG ETT ELLER ANNET ANNET
						+ "VALUES (?,?,?,?,?,?)");
				stmt.setInt(1, userId);
				stmt.setString(2, "DF."+item.getItemID());
				stmt.setString(3, explanation);
				stmt.setInt(4, 0);
				stmt.setInt(5, 0);
				stmt.setInt(6,ranking);
				result = stmt.executeUpdate();
			}
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
	
	/**Close connection to database*/
	public void closeConnection(){
		try {
			connection.close();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}
	
}
