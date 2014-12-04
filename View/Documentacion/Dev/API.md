API
===

La aplicación web provee una [API](https://es.wikipedia.org/wiki/API)
con diferentes funcionalidades implementadas a través de los controladores de la
aplicación.

La API utiliza las URLs como recursos utilizando las características del
protocolo HTTP, como la
[autenticación](https://es.wikipedia.org/wiki/Autenticaci%C3%B3n_de_acceso_b%C3%A1sica)
y los [códigos de respuesta](https://es.wikipedia.org/wiki/Anexo:C%C3%B3digos_de_estado_HTTP).
Además, todos los cuerpos de las solicitudes y de las respuestas están
codificados utilizando [JSON](https://es.wikipedia.org/wiki/JSON), incluyendo
errores. Todo lo anterior permite que cualquier cliente HTTP pueda comunicarse
con la API.

Recursos
--------

Los recursos, identificados por la URL, tienen la forma:

	{_url}/api/:controlador/:recurso

Donde *:controlador* es el controlador sobre el que está implementada la API y
*:recurso* es el recurso de la API sobre dicho controlador. El recurso no será
más que un método dentro del controlador que realizará una u otra acción
dependiendo del método HTTP usado.

Una solicitud simple a un recurso a través de GET, usando
[cURL](https://es.wikipedia.org/wiki/CURL), sería:

	$ curl {_url}/api/:controlador/:recurso

En el caso de recursos que estén en módulos, se deberá anteponer el módulo al
controlador de la siguiente forma:

	{_url}/api/:modulo/:controlador/:recurso

Autenticación
-------------

La autenticación en la API es realizada a través de
[HTTP Basic
Auth](https://es.wikipedia.org/wiki/Autenticaci%C3%B3n_de_acceso_b%C3%A1sica).
Las credenciales para autenticar pueden ser el usuario y la contraseña de la
aplicación, por ejemplo:

	$ curl -u usuario:contraseña {_url}/api/:controlador/:recurso

O bien el *hash* asociado a la cuenta del usuario, en cuyo caso la contraseña
es siempre una *X*, por ejemplo:

	$ curl -u hash:X {_url}/api/:controlador/:recurso

Recordar que si el *hash* se ve comprometido puede ser fácilmente cambiado en la
página del [perfil de usuario]({_base}/usuarios/perfil).

Las funcionalidades de la API que requieran autenticación estarán asociadas a
la cuenta del usuario que se use para autenticar y los permisos estarán
limitados a los que el usuario disponga en la aplicación.

Solicitudes
-----------

Todos los cuerpos de las solicitudes deben estar codificados usando JSON.

Por ejemplo, si una solicitud POST originalmente se viera así:

	campo1=valor1&campo2=valor2

Debe codificarse en JSON para que se vea así:

	{
		"campo1": "valor1",
		"campo2": "valor2"
	}

Lo mismo para los otros métodos HTTP usados.

### Métodos de HTTP

Se usan los siguientes métodos estándares de HTTP para indicar la intención de
una solicitud:

- **GET**: para recuperar un recurso o una colección de recursos.
- **PUT**: para actualizar un recurso envíando el recurso completo.
- **PATCH**: para actualizar un recurso envíando sólo partes del recurso.
- **DELETE**: para eliminar un recurso.
- **POST**: para crear un nuevo recurso. Además se utiliza para enviar datos a
funciones de la API de propósito general, no a operaciones
[CRUD](https://es.wikipedia.org/wiki/CRUD).

Respuestas
----------

Todos los cuerpos de las respuestas están codificados usando JSON.

Por ejemplo, un solo recurso es representado como un objeto JSON de la
siguiente forma:

	{
		"campo1": "valor",
		"campo2": true,
		"campo3": []
	}

Mientras que, una colección de objetos será representada como un arreglo de
objetos en JSON de la siguiente forma:

	[
		{
			"campo1": "valor",
			"campo2": true,
			"campo3": []
		},
		{
			"campo1": "otro valor",
			"campo2": false,
			"campo3": []
		}
	]

Adicionalmente pueden haber respuestas con un único valor, donde se entregará
por ejemplo *true*, *123* o *"Hola mundo!!"*.

Campos que no tengan un valor asignado serán representados con *null*. Si el
campo es un arreglo de datos será representado con un arreglo vacio *[]*.

### Códigos de estado HTTP

La API utiliza diferentes código de estado HTTP para indicar el éxito o fallo
de una solicitud. Algunos de estos códigos son:

#### Códigos de éxito:

- 200: solicitud exitosa, respuesta incluída.
- 201: recurso creado, la nueva URL al recurso está en la cabecera
*Location*.
- 204: solicitud exitosa, pero no hay contenido en la respuesta.

#### Códigos de error

- 400: solicitud errónea, no se ha podido procesar.
- 401: se requieren credenciales de autenticación o las que se han
proporcionado son incorrectas.
- 403: usuario autenticado no tiene permisos para la funcionalidad de la API.
- 404: funcionalidad (o recurso) no encontrado.
- 422: una solicitud para crear o modificar un recurso falló.
- 500, 501, 502, 503, etc: fallo interno del servidor.
