<?php
ini_set("memory_limit","1G");
$inverted_index=array();
//$fp=fopen("tweets_head.txt","r");
$filename="english_10k.txt";
//$filename="tweets_10k.txt";
$fp=fopen($filename,"r");
if(!$fp)
{
   echo "Cannot open the file $filename\n";
   exit(1);
}
//$fp=fopen("tweets_10k.txt","r");
$i=0;
echo "Building reverse index\n";
while($line=fgets($fp))
{
   if(!strlen(trim($line))) continue;
   //check for empty line
   //echo "Line=$line\n";
   $json_obj=json_decode($line,true);
   //print_r($json_obj);
   $data=$json_obj['Data'];
   //echo "Data=$data\n";
   $data_json_obj=json_decode($data,true);
   //print_r($data_json_obj);
   $id=$data_json_obj['IdStr'];
   $in_reply_to=$data_json_obj['InReplyToStatusIdStr'];
   $text=$data_json_obj['Text'];
   $time_of_tweet=$data_json_obj['CreatedAt'];
   $lang=$data_json_obj['Lang'];
   //echo "Text=$text time_of_tweet=$time_of_tweet\n";
   //exit();
   //echo "Id=$id in_reply_to=$in_reply_to\n";

   $i++;

   //Now create a hash of tweet_id ->text and timeof tweet for all tweets

   if($in_reply_to)
   {
      //this is a reply for id
      if(!@in_array($id,$replies[$in_reply_to]['children']))
         $replies[$in_reply_to]['children'][]=$id;
      $replies[$id]['parent']=$in_reply_to;

   }

   $tweet=array("id"=>$id,"text"=>$text,"time_of_tweet"=>$time_of_tweet,"lang"=>$lang);
   $tweets_array[$id]=$tweet;

   build_reverse_index($tweet);

}
fclose($fp);
print_r($inverted_index);
$tfx=tf("say","494633625082548224");
echo $tfx;
exit();
$stdin = fopen("php://stdin", "r");
while(1)
{
   echo "Enter the search string\n";
   $input_query=fgets($stdin);
   $input_query=trim($input_query);
   process_query($input_query);
}

function build_reverse_index($tweet)
{
   global $inverted_index;
   //print_r($tweet);
   $tweet_id=$tweet['id'];
   $text=$tweet['text'];

   //replace comma, semicolon etc
   $text=str_replace(","," ",$text);
   $text=str_replace(":"," ",$text);
   $text=str_replace("..."," ",$text);
   $text=str_replace(".."," ",$text);
   $words = explode(" ",$text);
   foreach($words as $word)
   {
      $word=process_word($word);
      //echo "Word returned=|$word|\n";
      if($word=="") continue;
      //echo "Adding $word to  index\n";
      $inverted_index[$word][]=$tweet_id;
   }
}


function tf($term,$tweet_id)
{
   global $tweets_array;
   $text=$tweets_array[$tweet_id]['text'];
   return substr_count ( $text , $term);
}

function process_query($input_query)
{
   global $tweets_array;
   global $replies;
   global $inverted_index;
   //echo "Entered process_query with $input_query\n";

   $terms=explode(" ",$input_query);
   $tweet_results=array();
   foreach($terms as $term)
   {
      $this_term_tweets=$inverted_index[$term];
      //echo "This term tweets for $term\n";
      //print_r($this_term_tweets);
      foreach($this_term_tweets as $result)
      {
         $tweet_hash[$result]++;
      }
   }
   print_r($tweet_hash);
   arsort($tweet_hash);
   //print_r($tweet_hash);
   foreach($tweet_hash as $result=>$val)
   {
      $final_result=$result;
      break;
   }
   $cx=count($replies);
   //echo "Count=$cx final_result=$final_result\n";
   $children=$replies[$final_result]['children'];
   //print_r($children);
   $result_tweet=$children[0];

   $text=$tweets_array[$result_tweet]['text'];
   echo "Result: $text\n";
}
function process_word($word)
{
   //echo "Entered pw with |$word|\n";
   $word=strtolower($word);
   switch($word)
   {
      case "a":
      case "an":
      case "the":
      case "it":
      case "rt":
      case "to":
         return "";
   }
   if(str_split($word)[0]=="@") return "";
   return $word;
}

function print_graph($tree,$node)
{
   /*Traverse the tree in  depth first order*/
   $children=array();
   $children=@$tree[$node]['children'];
   echo $node;
   if(!$children) return;
   echo "(";
   $len=count($children);
   $i=0;
   foreach($children as $child)
   {

      print_graph($tree,$child);
      if($i!=$len-1) echo ",";
      $i++;
   }
   echo ")";
}
