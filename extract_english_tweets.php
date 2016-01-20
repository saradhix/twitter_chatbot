<?php
ini_set("memory_limit","1G");
$inverted_index=array();
//$fp=fopen("tweets_head.txt","r");
$filename="tweets_2014_07_31_00.txt";
//$filename="tweets_10k.txt";
$fp=fopen($filename,"r");
$ft=fopen("english_tweets.txt","w");
if(!$fp)
{
    echo "Cannot open the file $filename\n";
    exit(1);
}
//$fp=fopen("tweets_10k.txt","r");
$i=0;
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

    if($lang=="en") //only english tweets
    {
        fputs($ft,"$line\n");
    }

}
fclose($fp);
fclose($ft);
