<?php
	require_once('init.php');
	use JasonKaz\FormBuild\Form as Form;
	use JasonKaz\FormBuild\Text as Text;
	use JasonKaz\FormBuild\Help as Help;
	use JasonKaz\FormBuild\Checkbox as Checkbox;
	use JasonKaz\FormBuild\Submit as Submit;
	use JasonKaz\FormBuild\Password as Password;
	use JasonKaz\FormBuild\Select as Select;
	use JasonKaz\FormBuild\Radio as Radio;			
	use JasonKaz\FormBuild\Button as Button;		
	use JasonKaz\FormBuild\Reset as Reset;
	use JasonKaz\FormBuild\Custom as Custom;
	use JasonKaz\FormBuild\Textarea as Textarea;
	use JasonKaz\FormBuild\Hidden as Hidden;
	use JasonKaz\FormBuild\Email as Email;
	use JasonKaz\FormBuild\Star as Star;
?>
<!DOCTYPE HTML>
<html lang-"en">
    <head>
        <title><?php Woodle(); ?></title>
        <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
        <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    </head>
    <body>

		<!-- Navbar -->
		<?php DisplayNavbar("textbooks.php"); ?>
        
        <div class="container">
            <div class="row-fluid">
				<!-- Sidebar -->
				<?php DisplaySidebar(); ?>
                <div class="span9">
                    <div class="row-fluid">
                    <?php TextbookReviewNav(false); ?>
                    </div>
                    <div class="row-fluid">
                     	<?php
                     		$dbc = new dbw(DBSERVER,DBUSER,DBPASS,DBCATALOG);
						
									$BookReviewForm=new Form;
									echo $BookReviewForm->init('','post',array('class'=>'form-horizontal'))
										->group('For course',
											new Select($dbc->queryPairs('SELECT Abbreviation,Description FROM Department WHERE RowOrder=1 ORDER BY RowOrder,Abbreviation'),1, array('class'=>'input-xlarge','name'=>'dept')),
											new Text(array('class'=>'input-medium','name'=>'course', 'placeholder'=>'Enter course number'))
										)
										->group('ISBN',
											new Text(array('class'=>'input-medium','name'=>'ISBN'))
										)
										->group('Usefulness', 
											new Star('use')
										)
										->group('Quality of content',
											new Star('qoc')
										)
										->group('Relevance to course',
											new Star('rtc')
										)
										->group('Additional comments',
											new Textarea('', array('class'=>'input-xlarge', 'rows'=>'8'))
										)
										->group('',
											new Submit('Submit', array('class' => 'btn btn-primary'))
										)
										->render();
                     	?>
                    </div>
                </div>
            </div>
        </div>
        
    </body>
    <script src="holder/holder.js"></script>
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <script src="bootstrap/raty/jquery.raty.js"></script>    
    <script src="bootstrap/raty/jquery.raty.min.js"></script>
    <script>
     $(function() {
    	$.fn.raty.defaults.path = 'bootstrap/raty/img';
    	//defaults were changed in jquery.raty.js and jquery.raty.min.js to half=true, score=2.5, and 
    	////hints=[very poor, poor, fair, good, very good]. Everything else in unchaged.
    	$('#use').raty({ half: true, score: 2.5 });
    	$('#qoc').raty({ half: true, score: 2.5  });
    	$('#rtc').raty({ half: true, score: 2.5  });
     });
    </script>
</html>

