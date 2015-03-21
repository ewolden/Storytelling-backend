package recommender;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collection;
import java.util.Iterator;
import java.util.LinkedHashMap;
import java.util.Spliterator;
import java.util.function.Consumer;
import java.util.function.Predicate;
import java.util.stream.Stream;

import org.apache.mahout.cf.taste.impl.similarity.GenericItemSimilarity.ItemItemSimilarity;


/* Computes the cosine similarity for all possible unique story-pair. In the test case, this is 9C2=36 pairs.
 * In our selection of stories this will be 167C2 = 13861 pairs*/

public class StoryCorrelations implements Collection<ItemItemSimilarity> {
		
	/*Testing values*/
	private LinkedHashMap<Integer, ArrayList<Integer>> storyCategories = new LinkedHashMap<Integer, ArrayList<Integer>>();
	/*The 9 0-1 values indicates whether the item(story) has the attribute(category) or not*/
	private ArrayList<Integer> row1 = new ArrayList<Integer>(Arrays.asList(1,1,0,0,1,0,0,0,0));
	private ArrayList<Integer> row2 = new ArrayList<Integer>(Arrays.asList(0,0,1,0,0,0,1,0,0));
	private ArrayList<Integer> row3 = new ArrayList<Integer>(Arrays.asList(1,1,0,0,1,0,1,0,0));
	private ArrayList<Integer> row4 = new ArrayList<Integer>(Arrays.asList(0,0,1,0,1,1,0,1,1));
	private ArrayList<Integer> row5 = new ArrayList<Integer>(Arrays.asList(0,1,0,0,1,1,0,1,1));
	private ArrayList<Integer> row6 = new ArrayList<Integer>(Arrays.asList(1,1,0,0,1,0,1,1,0));
	private ArrayList<Integer> row7 = new ArrayList<Integer>(Arrays.asList(1,0,1,0,1,1,0,0,0));
	private ArrayList<Integer> row8 = new ArrayList<Integer>(Arrays.asList(0,0,0,1,1,1,0,0,0));
	private ArrayList<Integer> row9 = new ArrayList<Integer>(Arrays.asList(1,1,0,0,1,1,0,0,0));
	
	private Collection<ItemItemSimilarity> result = new ArrayList<ItemItemSimilarity>();
	
	public StoryCorrelations() {
		initialize();
		compute();
	}

	private void initialize() {
		storyCategories.put(1, row1);
		storyCategories.put(2, row2);
		storyCategories.put(3, row3);
		storyCategories.put(4, row4);
		storyCategories.put(5, row5);
		storyCategories.put(6, row6);
		storyCategories.put(7, row7);
		storyCategories.put(8, row8);
		storyCategories.put(9, row9);
	}

	/*Computing the cosine similarities between all possible pairs of items (stories)*/
	public void compute() {
		Collection<ItemItemSimilarity> list = new ArrayList<ItemItemSimilarity>();
		for(int i=1; i<storyCategories.size(); i++){
			for(int j=i+1; j<=storyCategories.size(); j++){
				double value = computeCosineSimilarity(storyCategories.get(i), storyCategories.get(j));
				list.add(new ItemItemSimilarity(i, j, value));
			}
			
		}
		result.addAll(list);
	}

	public Collection<ItemItemSimilarity> getResult(){
		return result;
	}
	/* The actual cosine similarity computation
	 * Cosine similarity is chosen pretty much at random, other methods exists and may be better*/
	private double computeCosineSimilarity(ArrayList<Integer> item1List,
			ArrayList<Integer> item2list) {
		double sum = 0;
		double item1ListCount = 0;
		double item2ListCount = 0;
		for(int i=0; i<item1List.size(); i++){
			if (item1List.get(i) == 1){
				item1ListCount++;			
			}
			if (item2list.get(i) == 1){
				item2ListCount++;
			}
			if (item1List.get(i) == 1 && item2list.get(i) == 1){
				sum++;
			}
		}
		double similarity = sum/(Math.sqrt(item2ListCount)*Math.sqrt(item1ListCount));
		if(similarity > 1.0){
			similarity = 1.0;
		}
		return similarity;
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
