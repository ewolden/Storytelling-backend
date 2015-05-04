import static org.junit.Assert.assertEquals;

import java.io.FileInputStream;
import java.sql.SQLException;
import java.util.List;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.dbunit.IDatabaseTester;
import org.dbunit.JdbcDatabaseTester;
import org.dbunit.dataset.DataSetException;
import org.dbunit.dataset.IDataSet;
import org.dbunit.dataset.ITable;
import org.dbunit.dataset.xml.FlatXmlDataSetBuilder;
import org.dbunit.operation.DatabaseOperation;
import org.junit.AfterClass;
import org.junit.Before;
import org.junit.BeforeClass;
import org.junit.Test;


public class ContentBasedTest {

	static ContentBasedRecommender recommender;
	static IDatabaseTester tester;

	/**Run only once; before the first test*/
	@BeforeClass
	public static void initialSetUp(){
		try {
			/*Create a test-connection to the database*/
			tester = new JdbcDatabaseTester("com.mysql.jdbc.Driver", "jdbc:mysql://"+Globals.DB_HOST+"/testingDatabase", Globals.DB_USERNAME, Globals.DB_PASSWORD);
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
	
	/**Run before each test to make the database clean*/
	@Before
	public void setUp() throws Exception{
		FlatXmlDataSetBuilder builder = new FlatXmlDataSetBuilder();
		builder.setColumnSensing(true);
		/*Insert our test data*/
		IDataSet dataSet = builder.build(new FileInputStream("xml-files/setUp.xml"));
		tester.setSetUpOperation(DatabaseOperation.CLEAN_INSERT);
		tester.setDataSet(dataSet);
		tester.onSetup();
	}
	
	/**This is run after the last test*/
	@AfterClass
	public static void finalTearDown() throws Exception {
		tester.onTearDown();
	}

	/**Test to check if the recommender produced the correct number of recommendations when it should create new ones*/
	@Test
	public void NewContentBasedRecommendationsTest() throws DataSetException, SQLException, Exception{
		/*Create a recommender for user 1. We are not adding recommendations*/
		recommender = new ContentBasedRecommender(1,"false");
		/*Make sure we are connected to the test database*/
		recommender.conn.setDatabaseName("testingDatabase");
		/*Run the recommender for user 1*/
		try {
			recommender.runContentBasedRecommender();
		} catch (TasteException e) {
			e.printStackTrace();
		}	
		/*Get the produced recommendations*/
		List<RecommendedItem> recommendations = recommender.getRecommendations();
		
		/*In setUp.xml there are 5 preference values for user 1, so the recommender should find those 5 stories */
		assertEquals(5, recommendations.size());
		
		/* 2 of the 5 stories are rated and should therefore not be inserted in the database as recommendations.
		 * The stored_story table should then have 3 stories with an estimated_Rating > 0*/
		ITable insertedRecommendations = tester.getConnection().createQueryTable("RESULT", 
				"SELECT * FROM stored_story WHERE userId=1 AND estimated_Rating > 0");
		
		assertEquals(3,insertedRecommendations.getRowCount());
	}
	
	/**Test to check if the recommender produced the correct number of recommendations when it's adding recommendations to existing ones*/
	@Test
	public void AddContentBasedRecommendationsTest() throws DataSetException, SQLException, Exception{
		/*Create a recommender for user 1. We are now adding recommendations*/
		recommender = new ContentBasedRecommender(1,"true");
		/*Make sure we are connected to the test database*/
		recommender.conn.setDatabaseName("testingDatabase");
		/*Run the recommender for user 1*/
		try {
			recommender.runContentBasedRecommender();
		} catch (TasteException e) {
			e.printStackTrace();
		}	
		/*Get the produced recommendations*/
		List<RecommendedItem> recommendations = recommender.getRecommendations();
		
		/*In setUp.xml there are 5 preference values for user 1, so the recommender should find those 5 stories */
		assertEquals(5, recommendations.size());
		
		/* 3 of the 5 stories are rated or in the front end array and should therefore not be inserted in the database as recommendations.
		 * The stored_story table should then have 2 stories with an estimated_Rating > 0*/
		ITable insertedRecommendations = tester.getConnection().createQueryTable("RESULT", 
				"SELECT * FROM stored_story WHERE userId=1 AND estimated_Rating > 0");
		
		assertEquals(2,insertedRecommendations.getRowCount());
	}
}
