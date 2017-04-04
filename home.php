<?php ob_start(); ?>
<?php
	ini_set('display_errors', 1);
	require_once 'vendor/autoload.php';

	use Neoxygen\NeoClient\ClientBuilder;

	$client = ClientBuilder::create()
		   ->addConnection('default','http','localhost',7474,true,'neo4j','jagan')
			->setAutoFormatResponse(true)
			#->enableNewFormattingService()
	    ->build();	
?>

<?php
	if(!isset($_SESSION)) 
    { 
        session_start(); 
    }
	if(isset($_SESSION['username']))
    {
    	$_SESSION['username'] = $_SESSION['rootuser'];
    	if(isset($_GET['username'])){
    		$query = 'match (u:User {name:"'.$_GET["username"].'"}) return u';
			$result = $client->sendCypherQuery($query)->getResult();
			$temp = $result->get('u');
			$_SESSION['username'] = $temp -> getProperty('name');
			$_SESSION['handle'] = $temp -> getProperty('handle');
			$_SESSION['email'] = $temp -> getProperty('email');
    	}
    	$user = $_SESSION['username'];
		$query = 'match (u:User) where u.name ="'. $user .'" return u';
		$result = $client->sendCypherQuery($query)->getResult();
		$temp = $result->get('u');
    	$handle = $temp -> getProperty('handle');
    	$email = $temp -> getProperty('email');    		
    	// echo $email;
    }
	else{
		header('location:index.php');
	}	
?>
<?php
	if(isset($_POST["search"])) 
	{
       
			$_SESSION['searchText'] = $_POST["searchTweet"];
			header('location:search.php?');
			exit;
	}	
?>


<?php
	if(isset($_POST["tweet"])) 
	{
		$query = 'create (t1: Tweet {text:"'.$_POST["TweetText"].'",replyparent:[] ,parent:[] })';
		$result = $client->sendCypherQuery($query);
        $query1 = 'MATCH (u: Tweet) WHERE u.text="'.$_POST["TweetText"].'" RETURN u';
		$result1 = $client->sendCypherQuery($query1)->getResult();
        $var = $result1->getSingleNode('Tweet');
        $id = $var->getID();
        //echo $id;
        $query2 = 'MATCH (user2:User {name:"'.$_SESSION['rootuser'].'"}) , (t1:Tweet)
                    WHERE ID(t1)='.$id.'
                    CREATE(user2)-[r1:posts]->(t1)';
        $result2 = $client->sendCypherQuery($query2);
		header('location:home.php');
		exit;
	}	
?>
<?php
	if(isset($_POST["reply"])) 
	{
		 $query='create (t: Tweet {text:"'.$_POST["Replytext"].'" ,parent:[],replyparent:[] })';
         $result = $client->sendCypherQuery($query);
         $query1='MATCH(t: Tweet {text:"'.$_POST["Replytext"].'"})
				SET t.replyparent =t.replyparent+'.$_POST[parentid].'
				WITH t
				MATCH (u:User {name: "'. $_SESSION['rootuser'] .'"})
				CREATE(u)-[r1:posts]->(t)
				WITH u
				MATCH (t1:Tweet),(t: Tweet {text:"'.$_POST["Replytext"].'" })
				Where ID(t1)='.$_POST[parentid].'
				create(t1)-[r2:reply]->(t)';
	    $result1 = $client->sendCypherQuery($query1);
		header('location:home.php');
		exit;
	}	
?>
<?php
	if(isset($_POST["retweet"])) 
	{
		 $query='create (t: Tweet {text:"'.$_POST["Retweettext"].'" ,parent:[],replyparent:[] })';
         $result = $client->sendCypherQuery($query);
         $query1='MATCH(t: Tweet {text:"'.$_POST["Retweettext"].'"})
				SET t.parent =t.parent+'.$_POST[parentid].'
				WITH t
				MATCH (u:User {name: "'. $_SESSION['rootuser'] .'"})
				CREATE(u)-[r1:posts]->(t)
				WITH u
				MATCH (t1:Tweet),(t: Tweet {text:"'.$_POST["Retweettext"].'" })
				Where ID(t1)='.$_POST[parentid].'
				create(t1)-[r2:retweet]->(t)';
	    $result1 = $client->sendCypherQuery($query1);
		header('location:home.php');
		exit;
	}	
?>

<!DOCTYPE html>
<html>
<head>
	<title>Home | Twitter</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="css/new_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

	
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.js"></script>
	<script type="text/javascript" src="js/new.js"></script>

	<script src="js/jquery.min.js"></script>

	<header>
		<nav class="navbar navbar-default navbar-fixed-top" style="z-index: 1080;">
			<div class="col-xs-12" style="background-color: #1DA1F2;min-height: 50px; min-width: 100%;">
				<div class="col-xs-5">
					<ul class="nav navbar-nav" style="background-color: #1DA1F2">
		                <li class="active"><a href="home.php" style="background-color: #1DA1F2; color: white;font-size: 17px"><span class="glyphicon glyphicon-home" style="background-color: #1DA1F2; size:15px;color: white; margin-right: 10px;" aria-hidden="true"></span>Home</a></li>
		            </ul>
				</div>
				<div class="col-xs-2" style="text-align: center;top: 9px;">
					<a href="home.php"><img src="twitter.png" width="30px;" height="30px;"></a>
				</div>
				<div class="col-xs-5">
					<button class="btn navbar-btn navbar-right" style="margin-top: 10px">
		            	<a href="logout.php">
		            		<span class="glyphicon glyphicon-off" aria-hidden="true" item-height="30" item-width="30" style="margin-top: 0px"></span>
		            	</a>
		            </button>
		            <button type="button" class="tweet btn btn-success navbar-right  btn-info" data-toggle="modal" data-target="#myModal" style=" background-color: white;  margin: 10px;margin-left: -6;margin-top: 8px;">
		            	<a style="color: #1DA1F2;font-weight:bold;text-decoration: none;">Tweet</a> 
		            </button>
		            <!-- Modal -->
		            <div class="modal fade" id="myModal" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Compose new Tweet</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
		                                <textarea type="text" class="form-control" name="TweetText" rows="5" style="border-radius: 10px;" placeholder="What's Happening?"></textarea>
		                            </div>
		                            <div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="Tweet" name="tweet">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
		            <form class="navbar-form navbar-right" action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                <div class="input-group">
		                    <input type="text" name="searchTweet" class="form-control" style="border-radius: 25px;z-index: 1;width:250px " title="@ for names or # for tweets" placeholder="Twitter Search">
		                    <div class="input-group-btn" style="position:inherit ">
		                        <button class=" btn btn-default " type="submit" value="search" name="search"  style=" z-index:2;border-top-right-radius:25px;border-bottom-right-radius:25px; margin-left: -41px;height: 33px; ">
		                                <i class="glyphicon glyphicon-search "></i>
		                        </button>
		                    </div>
		                </div>
		            </form>
				</div>
			</div>
		</nav>

	</header>
</head>

<body style="background-color: #F8F8FF;">
		<div  style="background-color: #1DA1F2; min-height: 120px; max-width: 21%; border: 1px solid #c3c3c3; border-top-left-radius: 5px; border-top-right-radius: 5px;margin-left: 50px; margin-top: 80px;">
		</div>	

		<div  style="background-color: white; min-height: 120px; max-width: 21%; border: 1px solid #c3c3c3; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;margin-left: 50px; margin-top: -1px;">
			<span  style="color: black; text-align: center; padding: 15px; font-weight: bolder;font-size:150%;"><a href="profile.php" style="text-decoration: none;color: black; "><?php echo $user;?></a> <br> 
			</span>
			<span style="color: grey;font-weight: normal; padding: 15px;font-size:90%">
			  	<?php echo $handle;?>
				<br>&nbsp;  TWEETS &nbsp; &nbsp;&nbsp;&nbsp; FOLLOWING &nbsp;&nbsp;   &nbsp;&nbsp; FOLLOWERS
			</span>
			<br>
			<span style="color: #1DA1F2;font-weight: bolder;font-size:100%"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?php 

				 	$query = 'MATCH (u:User {name: "'. $user .'"})-[r1:posts]-(t:Tweet) RETURN t';
						$result = $client->sendCypherQuery($query)->getResult();
						$temp = $result->get('t');
						$tempsize = count($temp);
					 echo $tempsize;
				?>
				&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; 
				<?php 

				 	$query = 'MATCH (u:User {name: "'. $user .'"})-[r1:follows]->(t:User) RETURN t';
						$result = $client->sendCypherQuery($query)->getResult();
						$temp = $result->get('t');
						$tempsize = count($temp);
					 echo $tempsize;
					 ?>
					  &nbsp;&nbsp;   &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp;  &nbsp;&nbsp; &nbsp;
					  <?php 

				 	$query = 'MATCH (t:User)-[r1:follows]->(u:User {name: "'. $user .'"}) RETURN t';
						$result = $client->sendCypherQuery($query)->getResult();
						$temp = $result->get('t');
						$tempsize = count($temp);
					 echo $tempsize;
				?>
			</span>
		</div>	


		<div class="col-xs-12">
			<div class="col-xs-3"></div>
			<div class="col-xs-6" style="float: left;margin-top: -240px;">

				<?php
					$query = 'match (u:User) - [:follows] -> (v:User) where u.name ="'. $user .'" return v';
					$result = $client->sendCypherQuery($query)->getResult();
					$temp = $result->get('v');
					$tempsiz = count($temp);
                    $t = 0;
					if($tempsiz == 1)
					{   
						
                        $uname =  $temp -> getProperty('name');
                        $query = 'match (u:User) - [:posts] -> (t:Tweet) where u.name ="'. $uname .'" return t';
						$result = $client->sendCypherQuery($query)->getResult();
						$temp = $result->get('t');
						$tempsize = count($temp);
							

						if ($tempsize == 0) {
							// echo '<div class="panel panel-primary">';
							// echo '<div class="panel-heading" style="background-color: #1DA1F2;!important;"></div>';
							// echo '<div class="panel-body">';
							// echo '<p>'.$uname.' has not tweeted yet</p>';
							// echo '</div>';
							// echo '</div>';
						}
						else if ($tempsize == 1) 
						{
							
	                        
							echo '<div class="panel panel-primary">';

							echo '<div class="panel-heading" style="background-color: #1DA1F2;!important;font-weight: bolder;"><p><a href="profile.php?username='.$user.'" style="text-decoration:none;color:white;">' . $user. ' </a></p> </div>';
							echo '<div class="panel-body">';
							echo '<p style="text-align:center;">';
							echo '<div style="height:100px;text-align:center;">';
	                        
							echo $temp-> getProperty('text');	
							$i= count($temp->getProperty('parent'));
							$j= count($temp->getProperty('replyparent'));
	                        
					        $t++;
	                       	$pid = $temp->getID();
						   	echo $pid;
							$i = $i-1;
	                        $j=$j-1;
							$u=0;
							if($j>-1)
							{

								$k=$temp->getProperty('replyparent')[$j];
								$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
					            $result = $client->sendCypherQuery($query)->getResult();
					            $temp2 = $result->get('w');
							  	$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
					            $result = $client->sendCypherQuery($query2)->getResult();
					            $temp3 = $result->get('v');

								echo "<div style='color:##000000;'><p align='left'><span>Reply to:</span></p>"."</div>";	
							 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
								echo $temp3->getProperty('name') . " : ";
								echo $temp2->getProperty('text') . "<br><br>";
								echo '</div>';
							}

                            while($i > -1)
                            {
								$k=$temp->getProperty('parent')[$i];
								$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
					            $result = $client->sendCypherQuery($query)->getResult();
					            $temp2 = $result->get('w');
								$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
					            $result = $client->sendCypherQuery($query2)->getResult();
					            $temp3 = $result->get('v');

								echo "<div style='color:##000000;'><p align='left'><span>Retweet to:</span></p>"."</div>";	
							 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
								echo $temp3->getProperty('name') . " : ";
								echo $temp2->getProperty('text') . "<br><br>";
								echo '</div>';
                             
							 $i--;
                            }	
                            echo '</div>';
            
							$tweetid = $temp-> getId();
							$tweet = $temp -> getProperty('text');
							$query2 = 'match (u:User) - [:likes] -> (t:Tweet) where t.text ="'. $tweet .'" return u';
							$result2 = $client->sendCypherQuery($query2)->getResult();
							$temp2 = $result2->get('u');
							$tweetid = $temp->getID();
							$tempsize2 = count($temp2);

							echo	'<span class="input-group-addon"> 
								<button data-toggle="modal" id="rk'.$t.'" data-target="#replymodal'.$t.'"><i style="font-size:15px"  onmouseover="this.style.color=\'red\'" onmouseout="this.style.color=\'black\'"  class="fa">&#xf112;</i></button> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; ';
							
								if(!isset($_SESSION)) 
							    { 
							        session_start(); 
							    }
								
								$_SESSION['a'] = $user;


								echo "<i  onmouseover=\"this.style.color='red'\" onclick=\"document.location.href='lik.php?id=".$tweetid."'\" onmouseout=\"this.style.color='black'\" class=\"glyphicon glyphicon-heart\"  > </i>"; 
									
								if($tempsize2!=0)
								{
									echo  $tempsize2;
								} 
									 
								echo '</b>'; 	
								echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'; 
								echo '<button data-toggle="modal" id="rk'.$t.'" data-target="#retweetmodal'.$t.'"><i onmouseover="this.style.color=\'red\'" 
								   onmouseout="this.style.color=\'black\'" class="glyphicon glyphicon-retweet" > 
								   </i> </button>
							</span>';
      ?>
					
										 
										 	
					<!---modal1-->
					<div class="modal fade" id="replymodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Reply</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $temp -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Replytext" rows="4" style="border-radius: 10px;" placeholder="Reply..."></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $temp->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="reply" name="reply">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
		            <!-- Modal 2-->
		            <div class="modal fade" id="retweetmodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Retweet</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $temp -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Retweettext" rows="4" style="border-radius: 10px;" placeholder="What's Happening?"></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $temp->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="retweet" name="retweet">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
                	<?php

						echo '</p>';
						echo '</div>';
						echo '</div>';
					}
					else if ($tempsize > 1) 
					{
						foreach($temp as $var) 
						{
							echo '<div class="panel panel-primary">';
							echo "hkhkhkk";
							echo '<div class="panel-heading" style="background-color: #1DA1F2;!important;font-weight: bolder;" ><p><a href="profile.php?username='.$user.'" style="text-decoration:none;color:white;">' . $user. ' </a></p>  </div>';
							echo '<div class="panel-body">';
							echo '<p style="text-align:center;">';
							echo '<div style="height:100px;text-align:center;">';
							
						    echo $var -> getProperty('text')."<br>";
								$t++;	
							
							 $i= count($var->getProperty('parent'));
							 $j= count($var->getProperty('replyparent'));
							 $pid=$var->getID();
							 
							 $i=$i-1;
							 $j=$j-1;
							 $u=0;
							if($j>-1)
							{

								$k=$var->getProperty('replyparent')[$j];
								$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
					            $result = $client->sendCypherQuery($query)->getResult();
					            $temp2 = $result->get('w');
							  	$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
					            $result = $client->sendCypherQuery($query2)->getResult();
					            $temp3 = $result->get('v');

								echo "<div style='color:##000000;'><p align='left'><span>Reply to:</span></p>"."</div>";	
							 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
								echo $temp3->getProperty('name') . " : ";
							    echo $temp2->getProperty('text') . "<br><br>";
								echo '</div>';
							}
                            while($i > -1)
                            {
								$k=$var->getProperty('parent')[$i];
								$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
					            $result = $client->sendCypherQuery($query)->getResult();
					            $temp2 = $result->get('w');
								$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
					            $result = $client->sendCypherQuery($query2)->getResult();
					            $temp3 = $result->get('v');

								echo "<div style='color:##000000;'><p align='left'><span>Retweet to:</span></p>"."</div>";	
							 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
								echo $temp3->getProperty('name') . " : ";
								echo $temp2->getProperty('text') . "<br><br>";
								echo '</div>';
								break;
                             
							 $i--;
                            }
							
							echo '</div>';
							$tweetid = $var -> getId();
							$tweet = $var -> getProperty('text');
							$query2 = 'match (u:User) - [:likes] -> (t:Tweet) where t.text ="'. $tweet .'" return u';
							$result2 = $client->sendCypherQuery($query2)->getResult();
							$temp2 = $result2->get('u');
							$tweetid = $var->getID();
							$tempsize2 = count($temp2);									
			
							echo	'<span class="input-group-addon"> 
							<button data-toggle="modal" id="rk'.$t.'" data-target="#replymodal'.$t.'"><i style="font-size:15px"  onmouseover="this.style.color=\'red\'" onmouseout="this.style.color=\'black\'"  class="fa">&#xf112;</i></button> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; ';
													 
												if(!isset($_SESSION)) 
													    { 
													        session_start(); 
													    }
													
													$_SESSION['a'] = $user;


													echo "<i  onmouseover=\"this.style.color='red'\" onclick=\"document.location.href='lik.php?id=".$tweetid."'\" onmouseout=\"this.style.color='black'\" class=\"glyphicon glyphicon-heart\"  > </i>"; 
													
													if($tempsize2!=0)
													 {echo  $tempsize2;} 
													 
													 echo '</b>'; 	
													  echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'; 
													   echo '<button data-toggle="modal" id="rk'.$t.'" data-target="#retweetmodal'.$t.'"><i  onmouseover="this.style.color=\'red\'" 
													   onmouseout="this.style.color=\'black\'" class="glyphicon glyphicon-retweet" > 
													   </i> </button>
										 </span>';
      ?>
					
										 
										 	
					<!---modal1-->
					<div class="modal fade" id="replymodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Reply</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $var -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Replytext" rows="4" style="border-radius: 10px;" placeholder="Reply..."></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $var->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="reply" name="reply">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
		            <!-- Modal 2-->
		            <div class="modal fade" id="retweetmodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Retweet</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $var -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Retweettext" rows="4" style="border-radius: 10px;" placeholder="What's Happening?"></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $var->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="retweet" name="retweet">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
					
                <?php
						echo '</p>';
							echo '</div>';
							echo '</div>';
						}
					}
						
				}

						
				else if ($tempsiz != 0) 
				{
					foreach($temp as $var) 
					{
						$user1 =  $var -> getProperty('name');
					
						$query1 = 'match (u:User) - [:posts] -> (t:Tweet) where u.name ="'. $user1 .'" return t';
						$result1 = $client->sendCypherQuery($query1)->getResult();
						$temp1 = $result1->get('t');
						$tempsize1 = count($temp1);
					
						if($tempsize1 != 0)
						{

							if($tempsize1 == 1)
							{
                              
								echo '<div class="panel panel-primary">';
								echo '<div class="panel-heading" style="background-color: #1DA1F2;!important;font-weight: bolder;" ><p><a href="profile.php?username='.$user1.'" style="text-decoration:none;color:white;">' . $user1. ' </a></p> </div>';
								echo '<div class="panel-body">';
								echo '<p style="text-align:center;">';
								echo '<div style="height:100px;text-align:center;">';
							    echo $temp1 -> getProperty('text')."<br>";
									$t++;	
								
								 $i= count($temp1->getProperty('parent'));
								 $j= count($temp1->getProperty('replyparent'));
								 $pid=$temp1->getID();
								 
								 $i=$i-1;
								 $j=$j-1;
								 $u=0;
								if($j>-1)
								{

									$k=$temp1->getProperty('replyparent')[$j];
									$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
						            $result = $client->sendCypherQuery($query)->getResult();
						            $temp2 = $result->get('w');
								  	$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
					            $result = $client->sendCypherQuery($query2)->getResult();
					            $temp3 = $result->get('v');

								echo "<div style='color:##000000;'><p align='left'><span>Reply to:</span></p>"."</div>";	
							 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
								echo $temp3->getProperty('name') . " : ";
							
	                                        
									echo $temp2->getProperty('text') . "<br><br>";
									echo '</div>';
								
								}
	                            while($i > -1)
	                            {
									$k=$temp1->getProperty('parent')[$i];
									$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
						            $result = $client->sendCypherQuery($query)->getResult();
						            $temp2 = $result->get('w');
									$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
						            $result = $client->sendCypherQuery($query2)->getResult();
						            $temp3 = $result->get('v');

									echo "<div style='color:##000000;'><p align='left'><span>Retweet to:</span></p>"."</div>";	
								 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
									echo $temp3->getProperty('name') . " : ";
									echo $temp2->getProperty('text') . "<br><br>";
									echo '</div>';
									break;
	                             
								 $i--;
	                            }
	                            echo '</div>';
							
							
								$tweetid = $temp1 -> getId();
										$tweet = $temp1 -> getProperty('text');
										$query2 = 'match (u:User) - [:likes] -> (t:Tweet) where t.text ="'. $tweet .'" return u';
										$result2 = $client->sendCypherQuery($query2)->getResult();
										$temp2 = $result2->get('u');
										$tweetid = $temp1->getID();
										$tempsize2 = count($temp2);
							
								echo	'<span class="input-group-addon"> 
								<button data-toggle="modal" id="rk'.$t.'" data-target="#replymodal'.$t.'"><i style="font-size:15px"  onmouseover="this.style.color=\'red\'" onmouseout="this.style.color=\'black\'"  class="fa">&#xf112;</i></button> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; ';
											 
												if(!isset($_SESSION)) 
											    { 
											        session_start(); 
											    }
													
												$_SESSION['a'] = $user;


												echo "<i  onmouseover=\"this.style.color='red'\" onclick=\"document.location.href='lik.php?id=".$tweetid."'\" onmouseout=\"this.style.color='black'\" class=\"glyphicon glyphicon-heart\"  > </i>"; 
												
												if($tempsize2!=0)
												{
													echo  $tempsize2;
												} 
													 
											 echo '</b>'; 	
											  echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'; 
											   echo '<button data-toggle="modal" id="rk'.$t.'" data-target="#retweetmodal'.$t.'"><i  onmouseover="this.style.color=\'red\'" 
											   onmouseout="this.style.color=\'black\'" class="glyphicon glyphicon-retweet" > 
											   </i> </button>
										 </span>';
      ?>
					
										 
										 	
					<!---modal1-->
					<div class="modal fade" id="replymodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Reply</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $temp1 -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Replytext" rows="4" style="border-radius: 10px;" placeholder="Reply..."></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $temp1->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="reply" name="reply">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
		            <!-- Modal 2-->
		            <div class="modal fade" id="retweetmodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Retweet</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $temp1 -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Retweettext" rows="4" style="border-radius: 10px;" placeholder="What's Happening?"></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $temp1->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="retweet" name="retweet">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
					
	                <?php
						echo '</p>';
							echo '</div>';
							echo '</div>';
							}//$tempsize1 == 1 CLOSE
							else 
							{
                            
								foreach($temp1 as $var) 
								{
	                                    echo '<div class="panel panel-primary">';
									echo '<div class="panel-heading" style="background-color: #1DA1F2;!important;font-weight: bolder;" ><p><a href="profile.php?username='.$user1.'" style="text-decoration:none;color:white;">' . $user1. ' </a></p> </div>';
									echo '<div class="panel-body">';
									echo '<p style="text-align:center;">';
									echo '<div style="height:75px;text-align:center;">';
								    echo $var -> getProperty('text')."<br>";
										$t++;	
									
									 $i= count($var->getProperty('parent'));
									 $j= count($var->getProperty('replyparent'));
									 $pid=$var->getID();
									 
									 $i=$i-1;
									 $j=$j-1;
									 $u=0;
									if($j>-1)
									 {

										$k=$var->getProperty('replyparent')[$j];
										$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
							            $result = $client->sendCypherQuery($query)->getResult();
							            $temp2 = $result->get('w');
									  	$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
							            $result = $client->sendCypherQuery($query2)->getResult();
							            $temp3 = $result->get('v');

										echo "<div style='color:##000000;'><p align='left'><span>Reply to:</span></p>"."</div>";	
									 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
										echo $temp3->getProperty('name') . " : ";
										echo $temp2->getProperty('text') . "<br><br>";
										echo '</div>';
									 }
		                            while($i > -1)
		                            {
										$k=$var->getProperty('parent')[$i];
										$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
							            $result = $client->sendCypherQuery($query)->getResult();
							            $temp2 = $result->get('w');
										$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
							            $result = $client->sendCypherQuery($query2)->getResult();
							            $temp3 = $result->get('v');

										echo "<div style='color:##000000;'><p align='left'><span>Retweet to:</span></p>"."</div>";	
									 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
										echo $temp3->getProperty('name') . " : ";
										echo $temp2->getProperty('text') . "<br><br>";
										echo '</div>';
										break;
		                             
									 $i--;
		                            }
							
									echo "</div><br><br>";
										$tweetid = $var -> getId();
												$tweet = $var -> getProperty('text');
												$query2 = 'match (u:User) - [:likes] -> (t:Tweet) where t.text ="'. $tweet .'" return u';
												$result2 = $client->sendCypherQuery($query2)->getResult();
												$temp2 = $result2->get('u');
												$tweetid = $var->getID();
												$tempsize2 = count($temp2);
									
											
							
									echo	'<span class="input-group-addon"> 
									<button data-toggle="modal" id="rk'.$t.'" data-target="#replymodal'.$t.'"><i style="font-size:15px"  onmouseover="this.style.color=\'red\'" onmouseout="this.style.color=\'black\'"  class="fa">&#xf112;</i></button> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; ';
															 
														if(!isset($_SESSION)) 
															    { 
															        session_start(); 
															    }
															
															$_SESSION['a'] = $user;


															echo "<i  onmouseover=\"this.style.color='red'\" onclick=\"document.location.href='lik.php?id=".$tweetid."'\" onmouseout=\"this.style.color='black'\" class=\"glyphicon glyphicon-heart\"  > </i>"; 
															
															if($tempsize2!=0)
															 {echo  $tempsize2;} 
															 
															 echo '</b>'; 	
															  echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'; 
															   echo '<button data-toggle="modal" id="rk'.$t.'" data-target="#retweetmodal'.$t.'"><i  onmouseover="this.style.color=\'red\'" 
															   onmouseout="this.style.color=\'black\'" class="glyphicon glyphicon-retweet" > 
															   </i> </button>
												 </span>';
                            ?>
					
										 
										 	
					<!---modal1-->
					<div class="modal fade" id="replymodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Reply</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $var -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Replytext" rows="4" style="border-radius: 10px;" placeholder="Reply..."></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $var->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="reply" name="reply">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
		            <!-- Modal 2-->
		            <div class="modal fade" id="retweetmodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Retweet</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $var -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Retweettext" rows="4" style="border-radius: 10px;" placeholder="What's Happening?"></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $var->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="retweet" name="retweet">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
					
                <?php
						echo '</p>';
							echo '</div>';
							echo '</div>';
									}
							}
						}
					}
				}
                    $query = 'match (u:User) - [:posts] -> (t:Tweet) where u.name ="'. $user .'" return t';
					$result = $client->sendCypherQuery($query)->getResult();
					$temp = $result->get('t');
					$tempsize = count($temp);
				   
                     
					if ($tempsize == 0) {
					// 	echo '<div class="panel panel-primary">';
					// 	echo '<div class="panel-heading" style="background-color: #1DA1F2;!important;"></div>';
					// 	echo '<div class="panel-body">';
					// 	echo '<p>'.$user.' has not tweeted yet</p>';
					// 	echo '</div>';
					// 	echo '</div>';
					}
					else if ($tempsize == 1) {
						echo '<div class="panel panel-primary">';
						echo '<div class="panel-heading" style="background-color: #1DA1F2;!important;font-weight: bolder;"> <p><a href="profile.php?username='.$user.'" style="text-decoration:none;color:white;">' . $user. ' </a></p> </div>';
						echo '<div class="panel-body">';
						echo '<p style="text-align:center;">';
                        echo '<div style="height:100px;text-align:center;">';
						echo $temp-> getProperty('text');	
						$i= count($temp->getProperty('parent'));
						$j= count($temp->getProperty('replyparent'));
                        
				        $t++;
                       $pid = $temp->getID();
					   echo $pid;
							 $i=$i-1;
                            if($j>-1)
							 {

								$k=$temp->getProperty('replyparent')[$j];
								$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
					            $result = $client->sendCypherQuery($query)->getResult();
					            $temp2 = $result->get('w');
							  	$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
					            $result = $client->sendCypherQuery($query2)->getResult();
					            $temp3 = $result->get('v');

								echo "<div style='color:##000000;'><p align='left'><span>Reply to:</span></p>"."</div>";	
							 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
								echo $temp3->getProperty('name') . " : ";
								echo $temp2->getProperty('text') . "<br><br>";
								echo '</div>';
							 }

                            while($i > -1)
                            {
								$k=$temp->getProperty('parent')[$i];
								$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
					            $result = $client->sendCypherQuery($query)->getResult();
					            $temp2 = $result->get('w');
									$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
					            $result = $client->sendCypherQuery($query2)->getResult();
					            $temp3 = $result->get('v');

								echo "<div style='color:##000000;'><p align='left'><span>Retweet to:</span></p>"."</div>";	
							 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
								echo $temp3->getProperty('name') . " : ";
								echo $temp2->getProperty('text') . "<br><br>";
								
                             
							 $i--;
                            }	
                           echo '</div>';
                           
				           $tweetid = $temp -> getId();
										$tweet = $temp -> getProperty('text');
										$query2 = 'match (u:User) - [:likes] -> (t:Tweet) where t.text ="'. $tweet .'" return u';
										$result2 = $client->sendCypherQuery($query2)->getResult();
										$temp2 = $result2->get('u');
										$tweetid = $temp->getID();
										$tempsize2 = count($temp2);
							
									
					
							echo	'<span class="input-group-addon"> 
							<button data-toggle="modal" id="rk'.$t.'" data-target="#replymodal'.$t.'"><i style="font-size:15px"  onmouseover="this.style.color=\'red\'" onmouseout="this.style.color=\'black\'"  class="fa">&#xf112;</i></button> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; ';
												 
												if(!isset($_SESSION)) 
													    { 
													        session_start(); 
													    }
													
													$_SESSION['a'] = $user;


													echo "<i  onmouseover=\"this.style.color='red'\" onclick=\"document.location.href='lik.php?id=".$tweetid."'\" onmouseout=\"this.style.color='black'\" class=\"glyphicon glyphicon-heart\"  > </i>"; 
													
													if($tempsize2!=0)
													 {echo  $tempsize2;} 
													 
													 echo '</b>'; 	
													  echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'; 
													   echo '<button data-toggle="modal" id="rk'.$t.'" data-target="#retweetmodal'.$t.'"><i  onmouseover="this.style.color=\'red\'" 
													   onmouseout="this.style.color=\'black\'" class="glyphicon glyphicon-retweet" > 
													   </i> </button>
										 </span>';
      ?>
					
										 
										 	
					<!---modal1-->
					<div class="modal fade" id="replymodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Reply</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $var -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Replytext" rows="4" style="border-radius: 10px;" placeholder="Reply..."></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $temp->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="reply" name="reply">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
		            <!-- Modal 2-->
		            <div class="modal fade" id="retweetmodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Retweet</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $var -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Retweettext" rows="4" style="border-radius: 10px;" placeholder="Retweet..."></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $temp->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="retweet" name="retweet">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>

                <?php

						echo '</p>';
						echo '</div>';
						echo '</div>';
					}
					else if ($tempsize > 1) {
							
							
						foreach($temp as $var) 
						{
							echo '<div class="panel panel-primary">';
							echo '<div class="panel-heading" style="background-color: #1DA1F2;!important;font-weight: bolder;" ><p><a href="profile.php?username='.$user.'" style="text-decoration:none;color:white;">' . $user. ' </a></p> </div>';
							echo '<div class="panel-body">';
							echo '<p style="text-align:center;">';
							echo '<div style="height:100px;text-align:center;">';
						    echo $var -> getProperty('text')."<br>";
								$t++;	
							
							 $i= count($var->getProperty('parent'));
							 $j= count($var->getProperty('replyparent'));
							 $pid=$var->getID();
							 
							 $i=$i-1;
							 $j=$j-1;
							 $u=0;
							if($j>-1)
							 {

								$k=$var->getProperty('replyparent')[$j];
								$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
					            $result = $client->sendCypherQuery($query)->getResult();
					            $temp2 = $result->get('w');
							  	$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
					            $result = $client->sendCypherQuery($query2)->getResult();
					            $temp3 = $result->get('v');

								echo "<div style='color:##000000;'><p align='left'><span>Reply to:</span></p>"."</div>";	
							 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
								echo $temp3->getProperty('name') . " : ";
								echo $temp2->getProperty('text') . "<br><br>";
								echo '</div>';
							 }
                            while($i > -1)
                            {
								$k=$var->getProperty('parent')[$i];
								$query = 'match (w:Tweet) where ID(w) ='.$k.' return w';
					            $result = $client->sendCypherQuery($query)->getResult();
					            $temp2 = $result->get('w');
								$query2 = 'match (v:User)-[:posts]->(z:Tweet) where ID(z)='.$k.' return v';
					            $result = $client->sendCypherQuery($query2)->getResult();
					            $temp3 = $result->get('v');

								echo "<div style='color:##000000;'><p align='left'><span>Retweet to:</span></p>"."</div>";	
							 	echo '<div style="border: 2px solid #1DA1F2;text-align:center; border-radius: 5px ">'  ;
								echo $temp3->getProperty('name') . " : ";
								echo $temp2->getProperty('text') . "<br><br>";
								echo '</div>';
								break;
                             
							 $i--;
                            }
							
							echo '</div>';
								$tweetid = $var -> getId();
										$tweet = $var -> getProperty('text');
										$query2 = 'match (u:User) - [:likes] -> (t:Tweet) where t.text ="'. $tweet .'" return u';
										$result2 = $client->sendCypherQuery($query2)->getResult();
										$temp2 = $result2->get('u');
										$tweetid = $var->getID();
										$tempsize2 = count($temp2);
							
									
					
							echo	'<span class="input-group-addon"> 
							<button data-toggle="modal" id="rk'.$t.'" data-target="#replymodal'.$t.'"><i style="font-size:15px"  onmouseover="this.style.color=\'red\'" onmouseout="this.style.color=\'black\'"  class="fa">&#xf112;</i></button> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; ';
													 
												if(!isset($_SESSION)) 
													    { 
													        session_start(); 
													    }
													
													$_SESSION['a'] = $user;


													echo "<i  onmouseover=\"this.style.color='red'\" onclick=\"document.location.href='lik.php?id=".$tweetid."'\" onmouseout=\"this.style.color='black'\" class=\"glyphicon glyphicon-heart\"  > </i>"; 
													
													if($tempsize2!=0)
													 {echo  $tempsize2;} 
													 
													 echo '</b>'; 	
													  echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'; 
													   echo '<button data-toggle="modal" id="rk'.$t.'" data-target="#retweetmodal'.$t.'"><i  onmouseover="this.style.color=\'red\'" 
													   onmouseout="this.style.color=\'black\'" class="glyphicon glyphicon-retweet" > 
													   </i> </button>
										 </span>';
      ?>
					
										 
										 	
					<!---modal1-->
					<div class="modal fade" id="replymodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Reply</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $var -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Replytext" rows="4" style="border-radius: 10px;" placeholder="Reply..."></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $var->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="reply" name="reply">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
		            <!-- Modal 2-->
		            <div class="modal fade" id="retweetmodal<?php echo $t ?>" role="dialog">
		                <div class="modal-dialog modal-lg" style="width: 650px; margin-top: 100px;">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal">&times;</button>
		                            <h4 class="modal-title" style="text-align: center">Retweet</h4>
		                        </div>
		                        <form action=<?php echo htmlspecialchars($_SERVER[ "PHP_SELF"]); ?> method="post">
		                            <div class="modal-body form-control" style="height:150px;">
										<div style="border-radius: 5px;
										background: #adebeb;">
							        <?php echo $var -> getProperty('text');?></div>
			              <textarea  type="text" class="form-control" name="Retweettext" rows="4" style="border-radius: 10px;" placeholder="What's Happening?"></textarea>
										<input type = "hidden" name="parentid" value=" <?php echo $var->getID(); ?>">
									</div>
									<div class="modal-footer">
		                                <input type="submit" class="btn btn-success btn-info" value="retweet" name="retweet">
		                            </div>
		                        </form>
		                    </div>
		                </div>
		            </div>
					
                <?php
						echo '</p>';
							echo '</div>';
							echo '</div>';
						}
					}
				// }

    //  }
				?>
			</div>
			<div class="col-xs-3">
			</div>
		</div>
</body>
</html>