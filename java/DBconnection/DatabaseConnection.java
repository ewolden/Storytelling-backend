import java.util.Collection;

import javax.sql.PooledConnection;

import org.apache.mahout.cf.taste.common.Refreshable;
import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.model.jdbc.MySQLJDBCDataModel;
import org.apache.mahout.cf.taste.impl.model.jdbc.ReloadFromJDBCDataModel;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.model.JDBCDataModel;

import com.mysql.jdbc.jdbc2.optional.MysqlConnectionPoolDataSource;


public class DatabaseConnection {
	PooledConnection connection;
	DataModel model;
	ReloadFromJDBCDataModel reloadModel;

	/**Creates a connection to the database*/
	public DatabaseConnection() throws TasteException{
		MysqlConnectionPoolDataSource dataSource = new MysqlConnectionPoolDataSource();

		dataSource.setServerName(Globals.DB_HOST);
		dataSource.setUser(Globals.DB_USERNAME);
		//dataSource.setPortNumber(3306);
		dataSource.setPassword(Globals.DB_PASSWORD);
		dataSource.setDatabaseName(Globals.DB_NAME);
		try {
			dataSource.setCachePreparedStatements(true);
			dataSource.setCachePrepStmts(true);
			dataSource.setCacheResultSetMetadata(true);
			dataSource.setAlwaysSendSetIsolation(false);
			dataSource.setElideSetAutoCommits(true);
			connection = dataSource.getPooledConnection();

			JDBCDataModel dataModel = new MySQLJDBCDataModel(
					dataSource, "preference_value", "userId",
					"numericalId", "preferenceValue", "time_stamp");


			reloadModel = new ReloadFromJDBCDataModel(dataModel);
			if(reloadModel != null){
				model = reloadModel.getDelegateInMemory();

			} else model = dataModel;
			System.out.println(model.getMinPreference());
		} catch (Exception e) {
			// TODO Auto-generated catch block
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
	
	
}
