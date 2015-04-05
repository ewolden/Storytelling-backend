

/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import org.apache.mahout.cf.taste.common.Refreshable;
import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.common.FastIDSet;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.model.PreferenceArray;
import org.apache.mahout.cf.taste.recommender.CandidateItemsStrategy;
import org.apache.mahout.cf.taste.recommender.MostSimilarItemsCandidateItemsStrategy;

import java.util.Collection;

/**
 * Abstract base implementation for retrieving candidate items to recommend
 */

/* 
 * This is more or less copy-paste taken from this commit: https://github.com/apache/mahout/commit/d141c8e887904122a2b3cb4bf94851e7401d807d 
 * Needed to do this because the current version of Mahout doesn't support recommendation of already consumed items.
 * Seems like it's going to be supported in the next version though.
 */

public abstract class CustomAbstractCandidateItemsStrategy implements CandidateItemsStrategy,
    MostSimilarItemsCandidateItemsStrategy {

  protected FastIDSet doGetCandidateItems(long[] preferredItemIDs, DataModel dataModel) throws TasteException{
      return doGetCandidateItems(preferredItemIDs, dataModel, false);
  }
  
  public FastIDSet getCandidateItems(long userID, PreferenceArray preferencesFromUser, DataModel dataModel,
      boolean includeKnownItems) throws TasteException {
    return doGetCandidateItems(preferencesFromUser.getIDs(), dataModel, includeKnownItems);
  }
  
  public FastIDSet getCandidateItems(long[] itemIDs, DataModel dataModel)
    throws TasteException {
    return doGetCandidateItems(itemIDs, dataModel, false);
  }
     
  protected abstract FastIDSet doGetCandidateItems(long[] preferredItemIDs, DataModel dataModel,
      boolean includeKnownItems) throws TasteException;
  
  public void refresh(Collection<Refreshable> alreadyRefreshed) {}
}
