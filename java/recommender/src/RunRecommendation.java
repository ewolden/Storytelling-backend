package itemRecommender;

import java.util.ArrayList;

import org.apache.mahout.cf.taste.recommender.RecommendedItem;

public class RunRecommendation {
	
	public static void main(String[] args) {
		long userId = Long.parseLong(args[0]);
		System.out.println("UserId: "+userId);
		String method = args[1];
		System.out.println("Method: " + method);
		
		if(method.equals("content")){
			try {
				new ContentBasedRecommendation(userId);
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
		else if(method.equals("collaborative")){
			try{
				//new UserbasedRecommender(userId);
				ItemRecommender IR = new ItemRecommender(userId);
				
				/*itembased recommendations */
				ArrayList<CollaborativeRecommendation> itembased = new ArrayList<CollaborativeRecommendation>();
				ArrayList<CollaborativeRecommendation> userbased = new ArrayList<CollaborativeRecommendation>();
				/*Both itembased and userbased will be collected to this arraylist*/
				ArrayList<CollaborativeRecommendation> collaborativeRecommendations = new ArrayList<CollaborativeRecommendation>();

				itembased = IR.RunItemRecommender();
				for(CollaborativeRecommendation recommendation : itembased){
					collaborativeRecommendations.add(recommendation);
					//System.out.println("getitemid: "+ recommendation.getItem().getItemID());
				}
				UserbasedRecommender UR = new UserbasedRecommender(userId);
				userbased = UR.RunUserbasedRecommender();
				for(CollaborativeRecommendation recommendation : userbased){
					collaborativeRecommendations.add(recommendation);
				}
				for(CollaborativeRecommendation colRec : collaborativeRecommendations){
					System.out.println(colRec.getItem() +","+ userId + "," +colRec.getExplanation());
					DatabaseConnection db = new DatabaseConnection();
					
					db.insertUpdateRecommendValues(colRec.getItem(), (int)userId, colRec.getExplanation());
				}
				//System.out.println(collaborativeRecommendations.toString());
				

				//System.out.println("List of collaborative recommendations: " + collaborativeRecommendations[0].getItem());
			} catch (Exception e){
				e.printStackTrace();
			}
		}

		else if(method.equals("hybrid")){
			System.out.println("Hybrid recommending not yet implementing");
		}
		else {
			System.out.println("Wrong method");
		}

	}

}
