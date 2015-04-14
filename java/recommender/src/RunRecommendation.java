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
			System.out.println("Collaborative filtering not yet implemented");
		}
		else if(method.equals("hybrid")){
			System.out.println("Hybrid recommending not yet implementing");
		}
		else {
			System.out.println("Wrong method");
		}

	}

}
