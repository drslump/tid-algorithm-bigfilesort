# Problem

Given a log file very big, so much that it doesn't git in RAM memory,
design an algorithm to sort it by date.

Which is the complexity of your solution?

The format of a log line is: date user event


# Usage

php sort.php input.dat
php sort.php input.dat > output.dat


# Algorithm

Basically we're doing a Merge Sort but instead of using a recursive
function to split the set in half at each iteration, we split the
set in N bins each one having a maximum size which is known to
fit on main memory. We sort each bin individually and store the
result on a temporary file.

Once we have those N bins sorted on temporary files, we need to
keep merging their top rows into the output set until we have
consumed all the bins.

  - Keep consuming upto K rows from input
    - Sort these rows in memory
    - Store the sorted result in a temporary file
  - Keep merging the first row of each temp. file until all are consumed
    - Place the selected row in the output file


# Big-O

Being n the total number of rows and k the maximum number of rows per
temporary file.

Each input row needs 2 reads (from the original data set and from the 
temporary file) and is sorted at least twice (to produce the ordered
temporary file and upto K times when merging the top rows in each temp. file).
Additionally, it requires 2 writes (to the temporary file and to the 
final file).

The computing complexity offers a worst case scenario of O(k) and a best
case scenario of O(n/k).

Regarding the additional space, we have O(n) for the creation of the 
temporary files. 


# Optimizations

The first step (splitting and sorting the temporary files) is easily 
parallelizable.

The second step can be optimized by buffering the reads and the writes,
reading upto N rows from each bin, merging them and writing N rows
to the final file. The remainig rows are kept in the merge buffer for 
a new iteration.


