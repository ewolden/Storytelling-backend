

import java.util.ArrayList;

public class RunRecommendation {
	
	public static void main(String[] args) {
		long startTime = System.nanoTime();
		long userId = Long.parseLong(args[0]);
		System.out.println("UserId: "+userId);
		String method = args[1];
		System.out.println("Method: " + method);
		String add = args[2];
		System.out.println("Add?: " + add);
		
		if(method.equals("content")){
			try {
				ContentBasedRecommender cbr = new ContentBasedRecommender(userId, add);
				cbr.runContentBasedRecommender();
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
				ArrayList<DatabaseInsertObject> itemsToBeInserted = new ArrayList<>();
				for(CollaborativeRecommendation colRec : collaborativeRecommendations){
					System.out.println(colRec.getItem() +","+ userId + "," +colRec.getExplanation());		
					itemsToBeInserted.add(new DatabaseInsertObject((int)userId, "DF."+colRec.getItem().getItemID(), colRec.getExplanation(), 0, 1, 0));
					
				}
				DatabaseConnection db = new DatabaseConnection("collaborative_view");
				db.insertUpdateRecommendValues(itemsToBeInserted);
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

		long elapsedTime = System.nanoTime()-startTime;
    	System.out.println("Mahout time: "+(float)elapsedTime/1000000000);
	}

}
