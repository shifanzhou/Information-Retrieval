<?php

require_once("SpellCorrector.php");

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');


$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
$stillSearch = isset($_REQUEST['still_search']) ? $_REQUEST['still_search'] : false;
$useSpellCorrect = false;
$algo = "lucene";

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');
  $spellCheck = false;
  $query_terms = explode(" ", $query);
	$correct_terms = [];
	foreach ($query_terms as $term)
		$correct_terms[] = SpellCorrector::correct($term);
	echo "<script>console.log('" . array_values($correct_terms)[0] . "')</script>";
	$correct_query = implode(" ", $correct_terms);
	if (strtolower($query) != strtolower($correct_query))
		$spellCheck = true;
  

  
  

  // if magic quotes is enabled then stripslashes will be needed
  // if (get_magic_quotes_gpc() == 1)
  // {
  //   $query = stripslashes($query);
  // }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    if ($_GET['algo'] == "lucene"){
      $algo = "lucene";
      $results = $solr->search($query , 0, $limit);
    }else {
      $algo = "pageRank";
      $additionalParameters = array('sort' => 'pageRankFile desc');
      $results = $solr->search($query, 0, $limit, $additionalParameters);
    }
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
  <link href="http://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" rel="stylesheet">
	</link>
	<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
	<script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script>
   $(function() {
     var URL_PREFIX = "http://localhost:8983/solr/myexample/suggest?q=";
     var URL_SUFFIX = "&wt=json&indent=true";
     var count=0;

     $("#q").autocomplete({
       source : function(request, response) {
        var correct="",before="";
        var query = $("#q").val().toLowerCase();
        var character_count = query.length - (query.match(/ /g) || []).length;
        var space =  query.lastIndexOf(' ');
        if(query.length-1>space ){
          correct=query.substr(space+1);
          before = query.substr(0,space);
        }
        else{
          if (space == -1){correct=query.substr(0);}
          else{correct = query.substr(0,space)}
          
        }
        var URL = URL_PREFIX + correct+ URL_SUFFIX;
        console.log(URL);
        $.ajax({
         url : URL,

         success : function(data) {
          var tmp = data.suggest.suggest;

          // console.log(tmp, correct);
          var tags = tmp[correct]['suggestions'];
          var results = [];
          for(var i = 0; i < tags.length; i++){
            if(before===""){
              results.push(tags[i]['term']);
            }else{
              results.push(before+" "+tags[i]['term']);
            }
          }
          // console.log(results);
          // if (results[0] == correct || results[0] == before+" "+correct){
          //   results.shift();
          // }
          response(results);
        },
        dataType : 'jsonp',
        jsonp : 'json.wrf'
      });
      },
      minLength : 1
    })
   });
 </script>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input type="radio" id="lucene" name="algo" value="lucene"<?php if (isset($_REQUEST['algo']) && $_REQUEST['algo'] == 'lucene') {echo 'checked="checked"';}?>> Lucene
      <input type="radio" id="pageRank" name="algo" value="pageRank"<?php if (isset($_REQUEST['algo']) && $_REQUEST['algo'] == 'pageRank') {echo 'checked="checked"';}?>>Page Rank

      <input type="submit"/>
    </form>
<?php


// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
  if($spellCheck) {
    echo "Showing results for ", $query;
    $link = "?q=$correct_query&algo=$algo";
    echo "<br>Did you mean <a href='$link'>$correct_query</a>?";
  }
  
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  $csv = array_map('str_getcsv', file('/Users/shifanzhou/Downloads/URLtoHTML_fox_news.csv'));
  foreach ($results->response->docs as $doc)
  {
   
    $id = $doc->id;
    $title = $doc->title;
    $url = $doc->og_url;
    $desc = $doc->og_description;

    if ($desc == "" || $desc == null){
      $desc = "N/A";
    }

    if ($url == "" || $url == null){
      foreach($csv as $row){
        $cmp = "/Users/shifanzhou/Downloads/foxnews/".$row[0];
        if ($id == $cmp){
          $url = $row[1];
          unset($row);
          break;
        }
      }
    }

    echo "Title: <a href = '$url'>$title</a></br>";
    echo "URL: <a href = '$url'>$url</a></br>";
    echo "ID: $id</br>";
    echo "Description: $desc </br></br>";
    
    
  }
}
?>
    

  </body>
</html>
