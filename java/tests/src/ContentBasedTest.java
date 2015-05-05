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
 * Tests the ContentBasedRecommender-class
 * 
 * @author Audun Sæther
 * @author Kjersti Fagerholt 
 * @author Eivind Halmøy Wolden
 * @author Hanne Marie Trelease
 */

public class ContentBasedTest {

	/** An instance of the ContentBasedRecommender-class used in the test */
	static ContentBasedRecommender recommender;
	/** A connection to the database used for testing */
	static IDatabaseTester tester;

	/**
	 * Run only once; before the first test
	 */
	@BeforeClass
	public static void initialSetUp(){
		try {
			/*Create a test-connection to the database*/
			tester = new JdbcDatabaseTester("com.mysql.jdbc.Driver", "jdbc:mysql://"+Globals.DB_HOST+"/testingDatabase", Globals.DB_USERNAME, Globals.DB_PASSWORD);
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
	
	/**
	 * Run before each test to make the database clean
	 * 
	 * @throws Exception
	 */
	@Before
	public void setUp() throws Exception{
		FlatXmlDataSetBuilder builder = new FlatXmlDataSetBuilder();
		builder.setColumnSensing(true);
		/*Insert our test data*/
		IDataSet dataSet = builder.build(new FileInputStream("xml-files/setUp.xml"));
		/*CLEAN_INSERT deletes all data in the database before inserting new*/
		tester.setSetUpOperation(DatabaseOperation.CLEAN_INSERT);
		tester.setDataSet(dataSet);
		tester.onSetup();
	}
	
	/**
	 * This is run after the last test
	 * 
	 * @throws Exception
	 */
	@AfterClass
	public static void finalTearDown() throws Exception {
		tester.onTearDown();
	}

	/**
	 * Test to check if the recommender produced the correct number of recommendations when it should create new ones
	 * 
	 * @throws DataSetException
	 * @throws SQLException
	 * @throws Exception
	 */
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
	
	/**
	 * Test to check if the recommender produced the correct number of recommendations when it's adding recommendations to existing ones
	 * 
	 * @throws DataSetException
	 * @throws SQLException
	 * @throws Exception
	 */
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
