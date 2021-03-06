<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IIR</title>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load("current", {packages:["corechart"]});
        google.charts.setOnLoadCallback(drawChart);
        function drawChart() {
            var positif = parseInt(document.getElementById('positif').value);
            var negatif = parseInt(document.getElementById('negatif').value);
            var netral = parseInt(document.getElementById('netral').value);
            var data = google.visualization.arrayToDataTable([
                ['Title', 'Count'],
                ['Positif',     positif],
                ['Negatif',      negatif],
                ['Netral',      netral],
            ]);

            var options = {
                is3D: true,
            };

            var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
            chart.draw(data, options);
        }
    </script>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

    <!-- Style -->
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>
        <div class="menu-grid" style="display: flex;">
            <nav class="navbar navbar-expand-lg navbar-light mb-4" style="width: 145px; position: fixed; height: 100%; background-color: #20801d; border-bottom: 2px solid #386a99;" align="center">
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar mr-auto" style="list-style: none; display: block;">
                        <li class="nav-item active">
                            <a class="nav-link font-weight-bold" style="color: #FFF !important;" href="#">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" style="color: #FFF !important;" href="evaluasi.php">Evaluasi</a>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="menu mt-5" style="margin-left: 145px;">
                <div class="search pl-5 pr-5 mb-2">
                    <form method="GET" action="">
                        <input class="inp-keyword" type="text" name="search" placeholder="Input keyword">
                        <select name="metode" id="">
                            <option value="Overlap">Overlap</option>
                            <option value="Asymmetric">Asymmetric</option>
                            <option value="Dice">Dice</option>
                        </select>
                        <button type="submit">Search</button>
                    </form>
                </div>

                <div class="search pl-5 pr-5 mb-2">
                    <form method="POST" action="inputCSV.php" enctype="multipart/form-data">
                        <input class="inp-keyword" type="file" name="file" id="file" placeholder="Input keyword" style="width: 89%;">
                        <button id="submit" name="submit" type="submit">Submit</button>
                    </form>
                </div>

                <?php
                error_reporting(0);

                // Keyword
                $keyword = $_GET['search'];    
                $fix_keyword = str_replace(" ","-",$keyword);  
            
                // GET Data From URL
                $url = 'https://api.twitter.com/2/tweets/search/recent?query='.$fix_keyword;
                $options = array('http' => array(
                    'method'  => 'GET',
                    'header' => 'Authorization: Bearer AAAAAAAAAAAAAAAAAAAAAFdSWgEAAAAA19O4LNjaj86t2Cfd8Lo8OuK%2FTC0%3D431nIiXVvPCysUJ0q5m6eat4S0s6QPY7xGCyw1sTTyFVKpiakc'
                ));

                $context  = stream_context_create($options);
                $response = file_get_contents($url, false, $context);
                $data_tweets  = json_decode($response, true);
    
                // Data Processing 
                $arrayData      = [];
                $arrayTweets    = [];
                foreach ($data_tweets["data"] as $data) { 
                    error_reporting(0);

                    // Filter Text Tweets
                    preg_match_all('/@([^@\s]+)/', $data['text'], $username);
                    foreach($username as $valueUsername){
                        foreach($valueUsername as $fixUsername){
                            $usernames = $fixUsername;
                        }
                    }

                    $removeUsername = preg_replace('/(@[a-zA-Z0-9_]+)/', ' ', $data["text"]);
                    $removeLink = preg_replace('/(http|https|ftp|ftps):\/\/[a-zA-Z0-9-.]+.[a-zA-Z0-9]+(\/S*)?/', ' ', $removeUsername);
                    $removeEmoticon = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $removeLink);
                    $removePictographs = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $removeEmoticon);
                    $removeSymbols = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $removePictographs);
                    $removeMiscellaneous = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $removeSymbols);
                    $tweets = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $removeMiscellaneous);
                    $tweets = strtolower(trim($tweets));
                    $tweets = str_replace("'", " ", $tweets);
                    $tweets = str_replace('   "', " ", $tweets);
                    $tweets = str_replace("-", " ", $tweets);
                    $tweets = str_replace(")", " ", $tweets);
                    $tweets = str_replace("(", " ", $tweets);
                    $tweets = str_replace("\"", " ", $tweets);
                    $tweets = str_replace("/", " ", $tweets);
                    $tweets = str_replace("=", " ", $tweets);
                    $tweets = str_replace(".", " ", $tweets);
                    $tweets = str_replace(",", " ", $tweets);
                    $tweets = str_replace(":", " ", $tweets);
                    $tweets = str_replace(";", " ", $tweets);
                    $tweets = str_replace("!", " ", $tweets);
                    $tweets = str_replace("?", " ", $tweets);
                    $tweets = str_replace("rt", " ", $tweets);
                    $tweets = filter_var($tweets, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
                    $tweets = trim(preg_replace('/\s\s+/', ' ', $tweets));

                    // Input Data Tweets to Array
                    array_push($arrayData, $tweets);
                    array_push($arrayTweets, ['tweets' => [ $tweets , $data['id'] , $usernames]]);
                }  

                    // Filter Text Keyword
                    $filterKeyword = strtolower(trim($keyword));
                    $filterKeyword = trim(preg_replace('/\s\s+/', ' ', $filterKeyword));

                    // Input Data Keyword to Array
                    array_push($arrayData, $filterKeyword);

                    // Use PHP-ML
                    require_once __DIR__ . '/vendor/autoload.php';

                    use Phpml\FeatureExtraction\TokenCountVectorizer;
                    use Phpml\Tokenization\WhiteSpaceTokenizer;
                    use Phpml\FeatureExtraction\TfIdfTransformer;
                    $TF = new TokenCountVectorizer(new WhiteSpaceTokenizer());
                    $TF->fit($arrayData);
                    $TF->transform($arrayData);
                    
                    $vocabulary = $TF->getVocabulary();
                    $TFIDF = new TfIdfTransformer($arrayData);
                    $TFIDF->transform($arrayData);

                    $no=1;
                    $arrayTFIDF = [];
                    foreach ($arrayData as $data) {
                        array_push($arrayTFIDF, $data);
                        $no++;
                    }
                    
                    $totalData = count($arrayTFIDF[0]);
                    $totalBigData = count($arrayTFIDF); ?>
                        <div class="pl-5 pr-5">
                            <table class="table" style="width: 60vw;">
                                <thead>
                                    <tr>
                                    <th scope="col">No.</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Tweets</th>
                                    <th scope="col">Sentimen</th>
                                    </tr>
                                </thead>
                                <tbody>

                        <?php $y=1;
                        foreach ($arrayTweets as $data) { 
                            error_reporting(0);

                            $dataUsername = $data['tweets'][2];
                            $dataTweets = $data['tweets'][0];

                            // Metode Overlap
                            if($_GET['metode'] == "Overlap"){
                                $dQ = 0.0;
                                $d = 0.0;
                                $q = 0.0;
                                
                                for($i = 0; $i < $totalData-1; $i++)
                                {
                                    $dq += $arrayTFIDF[$totalBigData-1][$i]*$arrayTFIDF[$y][$i];
                                    $d += pow($arrayTFIDF[$y][$i], 2);
                                    $q += pow($arrayTFIDF[$totalBigData-1][$i], 2);
                                }
                                $result = $dQ/min($d, $q);
                            }
                            // Metode Asymmetric
                            elseif($_GET['metode'] == "Asymmetric"){
                                $dQ = 0.0;
                                $q = 0.0;
                                
                                for($i = 0; $i < $totalData-1; $i++)
                                {
                                    $dQ += min($arrayTFIDF[$totalBigData-1][$i], $arrayTFIDF[$y][$i]);
                                    $q += $arrayTFIDF[$totalBigData-1][$i];
                                }

                                $result = round($dQ/$q,1);
                            }
                            // Metode Dice
                            elseif($_GET['metode'] == "Dice"){
                                $dQ = 0.0;
                                $d = 0.0;
                                $q = 0.0;
                                
                                for($i = 0; $i < $totalData-1; $i++)
                                {
                                    $dQ += $arrayTFIDF[$totalBigData-1][$i]*$arrayTFIDF[$y][$i];
                                    $d += pow($arrayTFIDF[$y][$i], 2);
                                    $q += pow($arrayTFIDF[$totalBigData-1][$i], 2);
                                }

                                $result = $dQ/(0.5 * sqrt($q) + 0.5 * sqrt($d));
                            } ?>
                            <tr style="color: #868686;">
                                <th scope="row"><?=$y;?></th>
                                <td><?=$dataUsername?></td>
                                <td><?=$dataTweets?></td>

                                <?php if($result >= 1){ 
                                    $positif += 1; ?>
                                    <td>Positif</td>
                                <?php }elseif($result < 1 || $result >= 0.5){ 
                                    $negatif += 1; ?>
                                    <td>Negatif</td>
                                <?php }else{ 
                                    $netral += 1; ?>
                                    <td>Netral</td>
                                <?php } ?>
                            </tr>
                            
                        <?php 

                        include 'src/connection/connection.php';

                        // Overlap
                        $resultDQO = 0;
                        $resultDO = 0;
                        $resultQO = 0;
                        for($i = 0; $i < $totalData-1; $i++)
                        {
                            $resultDQO += ($arrayTFIDF[10][$i]*$arrayTFIDF[$y][$i]);
                            $resultDO += pow($arrayTFIDF[$y][$i], 2);
                            $resultQO += pow($arrayTFIDF[10][$i], 2);
                        }

                        $resultO = round($resultDQO/min($resultDO, $resultQO),1);

                        // Asymmetric
                        $resultDQA = 0;
                        $resultQA = 0;
                        
                        for($i = 0; $i < $totalData-1; $i++)
                        {
                            $resultDQA += (min($arrayTFIDF[10][$i], $arrayTFIDF[$y][$i]));
                            $resultQA += $arrayTFIDF[10][$i];
                            $resultDQ += (min($arrayTFIDF[$totalBigData-1][$i], $arrayTFIDF[$y][$i]));
                            $resultQ += $arrayTFIDF[$totalBigData-1][$i];
                            
                        }

                        $resultA = round($resultDQA/$resultQA,1);

                        // Dice
                        $resultDQD = 0;
                        $resultDD = 0;
                        $resultQD = 0;
                        
                        for($i = 0; $i < $totalData-1; $i++)
                        {
                        $resultDQD += ($arrayTFIDF[$y][$i] * $arrayTFIDF[10][$i]);
                        $resultDD += pow($arrayTFIDF[$y][$i], 2);
                        $resultQD += pow($arrayTFIDF[10][$i],2);
                        }

                        $resultD = round($resultDQD/(0.5 * sqrt($resultQD) + 0.5 * sqrt($resultDD)), 1);

                        $ids = $item["id"];

                        // Input to Database
                        $query = "INSERT INTO tweets (id_tweets, id_username, username, tweets, simil1, simil2, simil3) 
                        values 
                        (null, '$ids', '$dataUsername','$dataTweets','$resultO', '$resultA', '$resultD')";
                        mysqli_query($connect, $query);
                        $y++;
                        } ?>
                        
                            </tbody>
                        </table>
                        
                        <!-- Parameter Value use Pie Chart -->
                        <input type="hidden" value="<?=$positif?>" id="positif">
                        <input type="hidden" value="<?=$negatif?>" id="negatif">
                        <input type="hidden" value="<?=$netral?>" id="netral">
                    </div>
                </div>
                <div id="piechart_3d" style="width: 900px; height: 500px;"></div>
            </div>
        </div>
</body>
</html>