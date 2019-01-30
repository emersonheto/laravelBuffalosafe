<?php
// COMPROBACIÓN INICIAL ANTES DE CONTINUAR CON EL PROCESO DE UPLOAD
// **********************************************************************

// Si no se ha llegado ha definir el array global $_FILES, cancelaremos el resto del proceso
if (empty($_FILES['file-es'])) {
	// Devolvemos un array asociativo con la clave error en formato JSON como respuesta	
    echo json_encode(['error'=>'No hay ficheros para realizar upload error mensaje.']); 
	// Cancelamos el resto del script
   return; 
}

// DEFINICIÓN DE LAS VARIABLES DE TRABAJO (CONSTANTES, ARRAYS Y VARIABLES)
// ************************************************************************

// Definimos la constante con el directorio de destino de las descargas
define('DIR_DESCARGAS',__DIR__.DIRECTORY_SEPARATOR .'descargas1');

// Obtenemos el array de ficheros enviados
$ficheros = $_FILES['file-es'];
// Establecemos el indicador de proceso correcto (simplemente no indicando nada)
$estado_proceso = NULL;
// Paths para almacenar
$paths= array();
// Obtenemos los nombres de los ficheros
$nombres_ficheros = $ficheros['name'];



// LÍNEAS ENCARGADAS DE REALIZAR EL PROCESO DE UPLOAD POR CADA FICHERO RECIBIDO
// ****************************************************************************

// Si no existe la carpeta de destino la creamos
if(!file_exists(DIR_DESCARGAS)) @mkdir(DIR_DESCARGAS);
// Sólo en el caso de que exista esta carpeta realizaremos el proceso
if(file_exists(DIR_DESCARGAS)) {
	// Recorremos el array de nombres para realizar proceso de upload
	for($i=0; $i < count($nombres_ficheros); $i++){
		// Extraemos el nombre y la extensión del nombre completo del fichero
		$nombre_extension = explode('.', basename($nombres_ficheros[$i]));
		// Obtenemos la extensión
		$extension=array_pop($nombre_extension);
		// Obtenemos el nombre
		$nombre=array_pop($nombre_extension);
		// Creamos la ruta de destino
		$archivo_destino = DIR_DESCARGAS . DIRECTORY_SEPARATOR . utf8_decode($nombre) . '.' . $extension;
		// Mover el archivo de la carpeta temporal a la nueva ubicación
		if(move_uploaded_file($ficheros['tmp_name'][$i], $archivo_destino)) {
			// Activamos el indicador de proceso correcto
			$estado_proceso = true;
			// Almacenamos el nombre del archivo de destino
			$paths[] = $archivo_destino;
		} else {
			// Activamos el indicador de proceso erroneo		
			$estado_proceso = false;
			// Rompemos el bucle para que no continue procesando ficheros
			break;
		}
	}
}
// PREPARAR LAS RESPUESTAS SOBRE EL ESTADO DEL PROCESO REALIZADO
// **********************************************************************

// Definimos un array donde almacenar las respuestas del estado del proceso
$respuestas = array();
// Comprobamos si el estado del proceso a finalizado de forma correcta
if ($estado_proceso === true) {
	/* Podríamos almacenar información adicional en una base de datos
	   con el resto de los datos enviados por el método POST */

	// Como mínimo tendremos que devolver una respuesta correcta por medio de un array vacio.
    $respuestas = array();
	$respuestas = ['dirupload' => basename(DIR_DESCARGAS), 'total'=>count($paths)]; 
	/* Podemos devolver cualquier otra información adicional que necesitemos por medio de un array asociativo
       Por ejemplo, prodríamos devolver la lista de ficheros subidos de esta manera: 
       	$respuestas = ['ficheros' => $paths]; 
	   Posteriormente desde el evento fileuploaded del plugin iríamos mostrando el array de ficheros utilizando la propiedad response
	   del parámetro data: 
	   	respuesta = data.response; 
		respuesta.ficheros.forEach(function(nombre) {alert(nombre); });
	*/
} elseif ($estado_proceso === false) {
    $respuestas = ['error'=>'Error al subir los archivos. Póngase en contacto con el administrador del sistema'];
    // Eliminamos todos los archivos subidos
    foreach ($paths as $fichero) {
        unlink($fichero);
    }
// Si no se han llegado a procesar ficheros $estado_proceso seguirá siendo NULL
} else {
    $respuestas = ['error'=>'No se ha procesado ficheros.'];
}

// RESPUESTA DEVUELTA POR EL SCRIPT EN FORMATO JSON
// **********************************************************************

// Devolvemos el array asociativo en formato JSON como respuesta
echo json_encode($respuestas);
?>