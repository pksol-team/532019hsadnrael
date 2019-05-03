<?php
/*
* Plugin Name: Consulta Alumnos
* Plugin UR: http://www.e-learning.ninja
* Description: Cosulta los alumnos
* Version: 1.0
* Author: Patricio Bustamante M.
* Author URI: hhttp://www.e-learning.ninja
* License: GPL2
*/


add_shortcode( 'showexalumnos', 'display_custom_post_type_exalumnos' );

function display_custom_post_type_exalumnos(){

	$curso = isset($_GET['curso']) ? $_GET['curso'] : null ;
	$nombrealumno = isset($_GET['nombrealumno']) ? $_GET['nombrealumno'] : null ;

	ob_start();
	?>




	<form action="<?php echo get_permalink(); ?>" method="get" class="buscar">
	<input type="hidden" name="page_id" value="<?php echo page_id; ?>">
	<select name="curso" style="padding-top:8px; padding-bottom:5px;">
	 <option value="0"<?php if ($curso == 0) echo ' selected="selected"' ;?>>Todos los cursos</option>
	 <option value="1"<?php if ($curso == 1) echo ' selected="selected"' ;?>>Corretaje de propiedades</option>
	 <option value="2"<?php if ($curso == 2) echo ' selected="selected"' ;?>>Diplomado en Tasación inmobiliaria y agrícola</option>
	 <option value="3"<?php if ($curso == 3) echo ' selected="selected"' ;?>>Diplomado en Administración de empresas</option>
	 <option value="9"<?php if ($curso == 9) echo ' selected="selected"' ;?>>Cómo importar de china</option>
	 <option value="5"<?php if ($curso == 5) echo ' selected="selected"' ;?>>Administración hotelera</option>
	 <option value="8"<?php if ($curso == 8) echo ' selected="selected"' ;?>>Producción de eventos</option>
	</select>
	<p><input type="search" style="padding: 5px" name="nombrealumno"  value="<?php echo $nombrealumno; ?>" placeholder="escriba un nombre">
	<input type="submit" value="Filtrar" style="padding:5px 10px;"></p>
	</form>

	<?php


	$offset = 0;
	$page_result = 60; 
		
	if($_GET['pageno']) {
	 $page_value = $_GET['pageno'];
	 if($page_value > 1) {	
	  $offset = ($page_value - 1) * $page_result;
	 }
	}




    global $wpdb;
    $table_name = $wpdb->prefix . "posts";
    $table_name2 = $wpdb->prefix . "postmeta"; 

    if ( $curso > 0 ) {

    } else {
    	$curso = "";
    }



    if ( $curso <> "" && $nombrealumno <> "" ) {
    	$qtext = "SELECT * FROM         ".$table_name. ",".  $table_name2 . " where ID = post_id  and post_type = 'exalumno' and post_status = 'publish' and meta_key = 'ptb_curso' and meta_value like '%curso_".$curso."%'  and post_title like '%".$nombrealumno."%' order by post_date desc, post_title limit " .$offset . "," . $page_result; 
    	$qtext2 = "SELECT count(*) FROM ".$table_name. ",".  $table_name2 . " where ID = post_id  and post_type = 'exalumno' and post_status = 'publish' and meta_key = 'ptb_curso' and meta_value like '%curso_".$curso."%'  and post_title like '%".$nombrealumno."%'";
    }

    if ( $curso == "" && $nombrealumno <> "" ) {
    	$qtext = "SELECT * FROM         ".$table_name. ",".  $table_name2 . " where ID = post_id  and post_type = 'exalumno' and post_status = 'publish' and meta_key = 'ptb_curso' and post_title like '%".$nombrealumno."%' order by post_date desc , post_title limit " .$offset . "," . $page_result;
    	$qtext2 = "SELECT count(*) FROM ".$table_name. ",".  $table_name2 . " where ID = post_id  and post_type = 'exalumno' and post_status = 'publish' and meta_key = 'ptb_curso' and post_title like '%".$nombrealumno."%'";
    }    

    if ( $curso <> "" && $nombrealumno == "" ) {
    	$qtext = "SELECT * FROM         ".$table_name. ",".  $table_name2 . " where ID = post_id  and post_type = 'exalumno' and post_status = 'publish' and meta_key = 'ptb_curso' and meta_value like '%curso_".$curso."%'  order by post_date desc ,post_title limit " .$offset . "," . $page_result;
    	$qtext2 = "SELECT count(*) FROM ".$table_name. ",".  $table_name2 . " where ID = post_id  and post_type = 'exalumno' and post_status = 'publish' and meta_key = 'ptb_curso' and meta_value like '%curso_".$curso."%'";
    }       

     if ( $curso == "" && $nombrealumno == "" ) {
    	$qtext = "SELECT * FROM         ".$table_name." where post_type = 'exalumno' order by post_date desc, post_title limit " .$offset . "," . $page_result;
    	$qtext2 = "SELECT count(*) FROM ".$table_name." where post_type = 'exalumno'";
    }           

 


    // Use $wpdb prepare to be sure the query is properly escaped
    /*
    $qtext = "SELECT * FROM  ".$table_name. ",".  $table_name2 . " where ID = post_id  and post_type = 'exalumno'  ".$filtro." and post_status = 'publish' ". $querycurso . $queryalumno ." order by post_title limit " .$offset . "," . $page_result;
    $qtext2 = "SELECT count(*) FROM  ".$table_name. ",".  $table_name2 . " where ID = post_id  and post_type = 'exalumno'  ".$filtro." and post_status = 'publish' ". $querycurso . $queryalumno;
   
   	*/
    // echo $qtext;


    $results = $wpdb->get_results($qtext);

    // echo mysql_error();



    foreach($results as $results1) {
		$post_title = $results1->post_title;
		$ID = $results1->ID;
		// echo '<li>'.$post_title.'</li>';

		$table_name = $wpdb->prefix . "postmeta";

		$query2 = $wpdb->prepare("SELECT meta_value FROM " . $table_name ." where post_id = $ID and meta_key = 'ptb_comuna'", $user_id);
		// ECHO $query2;
		// Turn on error reporting, so you can see if there's an issue with the query
		$wpdb->show_errors();
		// Execute the query
		$results2 = $wpdb->get_row($query2);
		$comuna = $results2->meta_value; 

		$query2 = $wpdb->prepare("SELECT meta_value FROM " . $table_name ." where post_id = $ID and meta_key = 'ptb_fotografia'", $user_id);
		$results2 = $wpdb->get_row($query2);
		$key_1_value = get_post_meta( $ID, 'ptb_fotografia', true );


		$query3 = $wpdb->prepare("SELECT meta_value FROM " . $table_name ." where post_id = $ID and meta_key = 'ptb_curso'", $user_id);
		// echo $query3;
		$results3 = $wpdb->get_row($query3);
		$key_3_value = get_post_meta( $ID, 'ptb_curso', true );
		$codcurso = $key_3_value[0];
		$codcurso = str_replace("curso_","",$codcurso);

	    if ($codcurso == 1) $desccurso = 'Corretaje de propiedades';
	    if ($codcurso == 2) $desccurso = 'Diplomado en Tasación inmobiliaria y agrícola';
	 	if ($codcurso == 3) $desccurso = 'Diplomado en Administración de empresas';
	 	if ($codcurso == 9) $desccurso = 'Cómo importar de china';
	 	if ($codcurso == 5) $desccurso = 'Administración hotelera';
	 	if ($codcurso == 8) $desccurso = 'Producción de eventos';


		if ( $key_1_value[1] == 'http://braniffinstitute.com/cl/imagenes/' ) { $fotografia = 'http://braniffinstitute.com/cl/imagenes/no-imagen.png'; } else  { $fotografia = $key_1_value[1]; }

		$fotografia = trim($fotografia);

		?>


		<style type="text/css">
			.nombre {  font-size: 14px ; color: #000; padding-top:10px; border-bottom: 1px solid #ccc; width: 95%}
			.curso {  font-size: 13px }
			.comuna {  font-size: 12px }
			.foto  {   }
			.numpag { display:inline-block;padding:5px }
			
		</style>
		<div style="float:right; min-width: 360px; text-align: center; margin-bottom: 40px">
		<div style="display: block; overflow: hidden;width:100px;height:100px;  margin-left: auto;
    margin-right: auto;">
			<img class="foto" src="<?php echo $fotografia; ?>" width="100px" height="100px"/>
		</div>		
		<div class="nombre"><?php echo $post_title; ?></div>
		<div class="curso"><?php echo $desccurso; ?></div>
		<div class="comuna">&nbsp;<?php echo $comuna; ?>&nbsp;</div>
		</div>



		<?php




    }   


  
	$totreg = $wpdb->get_var( $qtext2);
    // echo "totreg".$totreg."<--";


	$pagecount = $totreg; // Total number of rows
	$num = $pagecount / $page_result ;

	?>
	<div style="clear: both; ">
	<?php

	echo "<div style='text-align:center'>";

	if  ( $_GET['pageno'] == "" ) $_GET['pageno'] = 1;

	for($i = 1 ; $i <= $num ; $i++) {
	 if ( $i ==  $_GET['pageno']  ) { $clasenum = ''; } else { $clasenum = 'style="text-decoration:underline"'; }
	 echo '<a  class="numpag" '.$clasenum.' href = "?pageno='. $i .'&curso='.$curso.'" >'. $i .'</a>';

	}

	echo "</div>";

	$output = ob_get_contents();
    ob_end_clean();

    return $output;
}




?>