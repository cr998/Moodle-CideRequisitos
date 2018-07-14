<?php

require_once(__DIR__."/../../../config.php");
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup("toolrequisitos");
$PAGE->set_url("http://aulavirtual35.academiaeninternet.com/admin/tool/requisitos/");



define("img_true","<img height='20px' src='img/true.png'/>");
define("img_false","<img height='20px' src='img/false.png'/>"); 

function tiene_codigo($curso){
    if($curso->idnumber!=""){
        return img_true;
    }else{
        return img_false;
    }
}

function precios_establecidos($curso){
    global $DB;
    $precio_paypal=$DB->get_record("enrol",array("enrol"=>"paypal","courseid"=>$curso->id));
    $precio_stripe=$DB->get_record("enrol",array("enrol"=>"stripepayment","courseid"=>$curso->id));
    if(!$precio_paypal || !$precio_stripe){
        return img_false;
    }
    return img_true;
}

function precios_iguales($curso){
    global $DB;
    $precio_paypal=$DB->get_record("enrol",array("enrol"=>"paypal","courseid"=>$curso->id));
    $precio_stripe=$DB->get_record("enrol",array("enrol"=>"stripepayment","courseid"=>$curso->id));
    if(!$precio_paypal || !$precio_stripe){
        return img_false;
    }
    
    if($precio_paypal->cost != $precio_stripe->cost){
        return img_false;
    }
    
    return img_true;
}

function examen_final($curso){
    global $DB;
    $section=$DB->get_record("course_sections",array("course"=>$curso->id,"name"=>"Examen Final"));
    if(!$section){
        return img_false;
    }
    return img_true;
}

function certificado($curso){
    $modinfo = get_fast_modinfo($curso->id);
    foreach($modinfo->get_cms() as $cm){
        if($cm->modname=="customcert"){
            return img_true;
        }
    }
    return img_false;
}

function img_summary($curso){
    if(!strpos($curso->summary,"img")){
        return img_true;
    }else{
        return img_false;
    }
}

function preguntas_cursos($curso){
    global $DB;
    $num=0;
    $context=$DB->get_record("context",array("instanceid"=>$curso->id,"contextlevel"=>50))->id;
    $categories=$DB->get_records('question_categories',array( 'contextid' => $context));
    foreach($categories as $cat){
        $quest=$DB->get_records("question",array("category"=>$cat->id));
        $num+=sizeof($quest);
    }
    return $num;
}

function formato_curso_temas($curso){
    if($curso->format=="topics"){
        return img_true;
    }else{
        return img_false;
    }
}










function estandarizado($curso){
    if
    (
        tiene_codigo($curso) == img_true &&
        precios_establecidos($curso) == img_true &&
        precios_iguales($curso) == img_true &&
        examen_final($curso) == img_true &&
        certificado($curso) == img_true &&
        img_summary($curso) == img_true &&
        formato_curso_temas($curso) == img_true
    ){
        return img_true;
    }else{
        return img_false;
    }
}


echo $OUTPUT->header();
echo $OUTPUT->heading("Requisitos", 2);

$table = new html_table();
$table->align = array('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center','center');
$table->head = array(
        "Curso",
        "Codigo curso",
        "Precios establecidos",
        "Precios iguales",
        "Examen final",
        "Certificado",
        "Imagen fuera de la descripción",
        "Preguntas por curso",
        "Formato de temas",
        "Estandarizado"
    );


$cursos = $DB->get_records_sql('SELECT * FROM {course} WHERE format != "site" ');

//var_dump($cursos[2]);

foreach ($cursos as $curso){
    $catname=@$DB->get_record('course_categories',array('id'=>$curso->category))->name;
    $data=array();
    $data[]="<a target='_blank' href='http://aulavirtual35.academiaeninternet.com/course/edit.php?id=".$curso->id."'>".$curso->fullname." (".$catname.")</a>";
    
    $data[]=tiene_codigo($curso)."</td>";
    $data[]=precios_establecidos($curso);
    $data[]=precios_iguales($curso);
    $data[]=examen_final($curso);
    $data[]=certificado($curso);
    $data[]=img_summary($curso);
    $data[]=preguntas_cursos($curso)."/20";
    $data[]=formato_curso_temas($curso);
    
    
    
    $data[]=estandarizado($curso);
    $table->data[]=$data;
}
    echo html_writer::table($table);
    

    echo $OUTPUT->footer();

?>