import static org.junit.Assert.assertEquals;

import java.io.FileInputStream;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;

import org.apache.mahout.cf.taste.impl.recommender.GenericRecommendedItem;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.dbunit.Assertion;
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


public class DatabaseConnectionTest {

	static DatabaseConnection connection;
	static IDatabaseTester tester;

	/*Done only once, before any of the tests are run*/
	@BeforeClass
	public static void initialSetUp() throws DataSetException, SQLException, Exception{
		try {
			/*Creates a test-connection to the testing database*/
			tester = new JdbcDatabaseTester("com.mysql.jdbc.Driver", "jdbc:mysql://"+Globals.DB_HOST+"/testingDatabase", Globals.DB_USERNAME, Globals.DB_PASSWORD);
		} catch (Exception e) {
			e.printStackTrace();
		}
		/*Creates a new instance of the class we shall test*/
		connection = new DatabaseConnection("content1");
		/*Make sure we are connected to our testing database*/
		connection.setDatabaseName("testingDatabase");
		connection.setConnection();	
	}
	
	/*Load our test data before every test*/
	@Before
	public void setUp() throws Exception{
		FlatXmlDataSetBuilder builder = new FlatXmlDataSetBuilder();
		builder.setColumnSensing(true);
		IDataSet dataSet = builder.build(new FileInputStream("xml-files/setUp.xml"));
		tester.setSetUpOperation(DatabaseOperation.CLEAN_INSERT);
		tester.setDataSet(dataSet);
		tester.onSetup();
	}
	
	/*Do this after the last test is run*/
	@AfterClass
	public static void finalTearDown() throws Exception {
		connection.closeConnection();
		tester.onTearDown();
	}
	
	/**Test to check if inserting of recommendations is working as expected*/
	@Test
	public void insertRecommendationsTest() throws SQLException, Exception{
		/*Create some recommendations and insert them into the database*/
		ArrayList<DatabaseInsertObject> recommendations = new ArrayList<>();
		DatabaseInsertObject recommend1 = new DatabaseInsertObject(1, "DF.1098", "", 0, 0, 3, 0);
		DatabaseInsertObject recommend2 = new DatabaseInsertObject(1, "DF.1501", "", 0, 0, 4, 0);
		recommendations.add(recommend1); 
		recommendations.add(recommend2);
		
		connection.insertUpdateRecommendValues(recommendations);
		
		//Load the XML-representation of how the table is suppose to be
		IDataSet expectedDataSet = new FlatXmlDataSetBuilder().build(new FileInputStream("xml-files/insert-expected.xml"));
		ITable expectedTable = expectedDataSet.getTable("stored_story");
		
		//Get the actual table from the database using our test-connection
		IDataSet actualDataSet = tester.getConnection().createDataSet();
		ITable actualTable = actualDataSet.getTable("stored_story");
		
		//Check that the expected table is the same as the actual table
		Assertion.assertEquals(expectedTable, actualTable);
		
	}
	
	/**Test to check if updating of recommendations work as expected*/
	@Test
	public void insertUpdateTest() throws SQLException, Exception{
		ArrayList<DatabaseInsertObject> recommendations = new ArrayList<>();
		
		/*Create two recommendations that should change the values for two recommendations inserted in the setUp*/
		DatabaseInsertObject recommend3 = new DatabaseInsertObject(1, "DF.1709", "updated", 1, 1, 3, 4.5);
		DatabaseInsertObject recommend4 = new DatabaseInsertObject(1, "DF.1849", "updated", 0, 1, 4, 2.5);
		recommendations.add(recommend3);
		recommendations.add(recommend4);
		
		/*Insert the new recommendation*/
		connection.insertUpdateRecommendValues(recommendations);
		
		/*Load the expected table*/
		IDataSet expectedDataSet = new FlatXmlDataSetBuilder().build(new FileInputStream("xml-files/insertUpdate-expected.xml"));
		ITable expectedTable = expectedDataSet.getTable("stored_story");
		
		/*Find the actual table in the database*/
		IDataSet actualDataSet = tester.getConnection().createDataSet();
		ITable actualTable = actualDataSet.getTable("stored_story");
		
		/*Check that the expected table is the same as the actual table*/
		Assertion.assertEquals(expectedTable, actualTable);
	}
	
	/**Test to check if the right stories are deleted
	 * @throws Exception 
	 * @throws SQLException */
	@Test
	public void deleteTest() throws SQLException, Exception{
		/* This should set all recommend_rankings to null and remove stories where in_frontend_array=0 AND stateId IS NULL (= story has not been seen by the user)
		 * In our test data, DF.1812 and DF.1849 has been seen by the user and should not be removed. DF.1901 is in the front end array and should not be removed
		 * DF.1709 is neither seen or in the front end array, and should therefore be removed */
		connection.deleteRecommendations(1);
		
		/*Load the expected table*/
		IDataSet expectedDataSet = new FlatXmlDataSetBuilder().build(new FileInputStream("xml-files/delete-expected.xml"));
		ITable expectedTable = expectedDataSet.getTable("stored_story");
		
		/*Find the actual table in the database*/
		IDataSet actualDataSet = tester.getConnection().createDataSet();
		ITable actualTable = actualDataSet.getTable("stored_story");
		
		/*Check that the expected table is the same as the actual table*/
		Assertion.assertEquals(expectedTable, actualTable);
		
	}
	
	/**Test if the getRated-method gets the right values*/
	@Test
	public void ratedTest(){
		HashMap<Integer, Integer> ratedStories = connection.getRated(1);
		
		/*The ratings we put into the database in setUp and are expected to get out using the getRated method*/
		HashMap<Integer, Integer> expectedRatings = new HashMap<>();
		expectedRatings.put(1812, 3);
		expectedRatings.put(1901, 5);
		
		assertEquals(expectedRatings,ratedStories);
	}
	
	/**Test if the method return the expected list of stories in front end array*/
	@Test
	public void inFrontendArrayTest(){
		ArrayList<Integer> frontendStories = connection.getStoriesInFrontendArray(1);
		
		/*The expected stories are the ones we sat to be in the front end array in setUp.xml*/
		ArrayList<Integer> expectedStories = new ArrayList<>();
		expectedStories.add(1849);
		expectedStories.add(1901);
		
		assertEquals(frontendStories, expectedStories);
	}
	
	/**Testing if createExplanation return the expected string*/
	@Test
	public void createExplanationTest(){
		/*Create some recommendedItems to use in the explanation*/
		ArrayList<RecommendedItem> explanationItems = new ArrayList<>();
		RecommendedItem item1 = new GenericRecommendedItem(1098, 0);
		RecommendedItem item2 = new GenericRecommendedItem(1115, 0);
		RecommendedItem item3 = new GenericRecommendedItem(1501, 0);
		explanationItems.add(item1);
		explanationItems.add(item2);
		explanationItems.add(item3);
		
		/*Get the actual string produced by the method*/
		String actualString = connection.createExplanation(explanationItems);
		
		/*The result we are expecting*/
		String expectedString = "DF.1098:Legeliv i Trondhjem,DF.1115:Evig eies kun det teipte,DF.1501:Dr. Pinnebergs jul - et arkivmysterium i to akter";
		
		assertEquals(expectedString, actualString);
	}
	
}
