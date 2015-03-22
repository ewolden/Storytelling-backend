package recommender;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collection;
import java.util.Iterator;
import java.util.Spliterator;
import java.util.function.Consumer;
import java.util.function.Predicate;
import java.util.stream.Stream;

import org.apache.mahout.cf.taste.impl.similarity.GenericItemSimilarity.ItemItemSimilarity;


/* Computes the cosine similarity for all possible unique story-pair. In the test case, this is 9C2=36 pairs.
 * In our selection of stories this will be 167C2 = 13861 pairs*/

public class StoryCorrelations implements Collection<ItemItemSimilarity> {
		
	/*Testing values
	/*The first integer is the storyId, the 9 0-1 values indicates whether the item(story) has the attribute(category) or not*/
	private ArrayList<Integer> row1 = new ArrayList<Integer>(Arrays.asList(15,1,1,0,0,1,0,0,0,0));
	private ArrayList<Integer> row2 = new ArrayList<Integer>(Arrays.asList(13,0,0,1,0,0,0,1,0,0));
	private ArrayList<Integer> row3 = new ArrayList<Integer>(Arrays.asList(12,1,1,0,0,1,0,1,0,0));
	private ArrayList<Integer> row4 = new ArrayList<Integer>(Arrays.asList(11,0,0,1,0,1,1,0,1,1));
	private ArrayList<Integer> row5 = new ArrayList<Integer>(Arrays.asList(14,0,1,0,0,1,1,0,1,1));
	private ArrayList<Integer> row6 = new ArrayList<Integer>(Arrays.asList(43,1,1,0,0,1,0,1,1,0));
	private ArrayList<Integer> row7 = new ArrayList<Integer>(Arrays.asList(24,1,0,1,0,1,1,0,0,0));
	private ArrayList<Integer> row8 = new ArrayList<Integer>(Arrays.asList(65,0,0,0,1,1,1,0,0,0));
	private ArrayList<Integer> row9 = new ArrayList<Integer>(Arrays.asList(34,1,1,0,0,1,1,0,0,0));
	private ArrayList<ArrayList<Integer>> storyCategories = new ArrayList<ArrayList<Integer>>();	
	
	private Collection<ItemItemSimilarity> result = new ArrayList<ItemItemSimilarity>();
	
	public StoryCorrelations() {
		initialize();
		compute();
	}

	private void initialize() {
		storyCategories.add(row1);
		storyCategories.add(row2);
		storyCategories.add(row3);
		storyCategories.add(row4);
		storyCategories.add(row5);
		storyCategories.add(row6);
		storyCategories.add(row7);
		storyCategories.add(row8);
		storyCategories.add(row9);
	}

	/*Computing the similarities between all possible pairs of items (stories)*/
	public void compute() {
		Collection<ItemItemSimilarity> list = new ArrayList<ItemItemSimilarity>();
		for(int i=0; i<storyCategories.size(); i++){
			for(int j=i+1; j<storyCategories.size(); j++){
				double cosine = computeCosineSimilarity(storyCategories.get(i), storyCategories.get(j));
				//double euclid = computeEuclideanDistance(storyCategories.get(i), storyCategories.get(j));
				//double jaccard = computeJaccardCoefficient(storyCategories.get(i), storyCategories.get(j));
				int itemId1 = storyCategories.get(i).get(0);
				int itemId2 = storyCategories.get(j).get(0);
				list.add(new ItemItemSimilarity(itemId1, itemId2, cosine));
			}
			
		}
		result.addAll(list);
	}

	public Collection<ItemItemSimilarity> getResult(){
		return result;
	}
	
	private double computeCosineSimilarity(ArrayList<Integer> item1List,
			ArrayList<Integer> item2List) {
		double sum = 0;
		double item1ListCount = 0;
		double item2ListCount = 0;
		for(int i=1; i<item1List.size(); i++){
			if (item1List.get(i) == 1){
				item1ListCount++;			
			}
			if (item2List.get(i) == 1){
				item2ListCount++;
			}
			if (item1List.get(i) == 1 && item2List.get(i) == 1){
				sum++;
			}
		}
		double similarity = sum/(Math.sqrt(item2ListCount)*Math.sqrt(item1ListCount));
		if(similarity > 1.0){
			similarity = 1.0;
		}
		return similarity;
	}

	private double computeEuclideanDistance(ArrayList<Integer> item1List, 
			ArrayList<Integer> item2List){
		double sim = 0;
		double sum = 0;
		for(int i=1; i<item1List.size(); i++){
			sum += Math.pow(item1List.get(i)-item2List.get(i),2);
		}		
		sim = Math.sqrt(sum);
		/*Return a normalized euclidean distance*/
		return 1/(1+sim);
	}
	
	private double computeJaccardCoefficient(ArrayList<Integer> item1List, 
			ArrayList<Integer> item2List){
		double coeff = 0;
		double m11 = 0;
		double m01 = 0;
		double m10 = 0;
		for(int i=0; i<item1List.size(); i++){
			if (item1List.get(i)==1){
				if (item2List.get(i)==1){
					m11++;
				}
				else{
					m10++;
				}
			}
			else {
				if(item2List.get(i)==1){
					m01++;
				}
			}
		}
		coeff = m11/(m10+m01+m11);
		return coeff;
	}
	
	public void forEach(Consumer<? super ItemItemSimilarity> arg0) {
		// TODO Auto-generated method stub
		
	}

	public boolean add(ItemItemSimilarity arg0) {
		// TODO Auto-generated method stub
		return false;
	}

	public boolean addAll(Collection<? extends ItemItemSimilarity> arg0) {
		// TODO Auto-generated method stub
		return false;
	}

	public void clear() {
		// TODO Auto-generated method stub
		
	}

	public boolean contains(Object arg0) {
		// TODO Auto-generated method stub
		return false;
	}

	public boolean containsAll(Collection<?> arg0) {
		// TODO Auto-generated method stub
		return false;
	}

	public boolean isEmpty() {
		// TODO Auto-generated method stub
		return false;
	}

	public Iterator<ItemItemSimilarity> iterator() {
		Iterator<ItemItemSimilarity> it = result.iterator();
		return it;
	}

	public Stream<ItemItemSimilarity> parallelStream() {
		// TODO Auto-generated method stub
		return null;
	}

	public boolean remove(Object arg0) {
		// TODO Auto-generated method stub
		return false;
	}

	public boolean removeAll(Collection<?> arg0) {
		// TODO Auto-generated method stub
		return false;
	}

	public boolean removeIf(Predicate<? super ItemItemSimilarity> arg0) {
		// TODO Auto-generated method stub
		return false;
	}

	public boolean retainAll(Collection<?> arg0) {
		// TODO Auto-generated method stub
		return false;
	}

	public int size() {
		// TODO Auto-generated method stub
		return 0;
	}

	public Spliterator<ItemItemSimilarity> spliterator() {
		// TODO Auto-generated method stub
		return null;
	}

	public Stream<ItemItemSimilarity> stream() {
		// TODO Auto-generated method stub
		return null;
	}

	public Object[] toArray() {
		// TODO Auto-generated method stub
		return null;
	}

	public <T> T[] toArray(T[] arg0) {
		// TODO Auto-generated method stub
		return null;
	}

	

}
