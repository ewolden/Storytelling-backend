
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
			int numberOfStoriesRecommended = 0;
			try {
				CollaborativeRecommender collaborativeRecommender = new CollaborativeRecommender(userId, add);
				numberOfStoriesRecommended = collaborativeRecommender.runCollaborativeRecommender();
				
				if(numberOfStoriesRecommended < 10 && false){ //disabled
					/* There are not enough collaborative recommendations, need content based in addition */
					ContentBasedRecommender cbr = new ContentBasedRecommender(userId, add);
					cbr.runContentBasedRecommender();
					MixedFiltering mixed = new MixedFiltering(userId);
					mixed.runMixedFiltering(cbr, collaborativeRecommender);
				}
				
			} catch (Exception e) {
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
