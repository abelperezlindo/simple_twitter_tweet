# Simple Twitter Tweet
> This is a module for drupal 9 which allows you to create tweets in a twitter account using the content of the site as a base.
## Funcionamiento

Cuando se cree un nuevo contenido del tipo seleccionado, se
recuperara de este la información necesaria para crear el 
tweet en el feed del usuario.
En la versión actual, no se publica cuando un nodo es actualizado, solo cuando es insertado.

## Requisitos
> **Nota:** Requiere PHP 7.4 o superior
### Necesitará instalar los módulos de drupal:
- hook_post_action 
  Instale usando composer : ``` composer require 'drupal/hook_post_action:^1.1' ```
- pub_options 
  Instale usando composer : ``` composer require 'drupal/pub_options:^2.0' ```
#### Necesitará instalar las librerías:
- twitteroauth
  Instale usando composer : ``` composer require 'abraham/twitteroauth:^4.0', ```


## Instalación
> The use of "composer" for the installation of this module is not yet available.
- Clone este repositorio o descomprima el archivo descargado en la carpeta de módulos de drupal.
- Instale este módulo siguiendo el procedimiento típico. 
See https://www.drupal.org/docs/extending-drupal/installing-modules

## Configuración 
### Cree una app en Twitter developer portal:
- Ingrese en https://developer.twitter.com/en/portal/dashboard y cree una app
- Solicite acceso elevado a la Api v2, necesario para obtener el permiso "Manage Tweets", es facil, solo deberá escribir 
en un formulario el "para que necesita el acceso elevado" 
- Copie el API key and Secret para usarlo mass tarde.
- Genere el Acces token and Secret con permisos de escritura y de lectura

### Configurar el contenido del sitio
- Instale el modulo requerido Publishing option y cree una nueva opción de publicación para el contenido deseado. Como sugerencia puede usar "Publicar en Twitter".
- Ingrese al formulario de configuración del modulo en "admin/config/stt/adminsettings".
- En "Tipo de contenido" ingrese el nombre de maquina del contenido que quiere utilizar.
- Ingrese en "Field to use to generate the text of the post" el nombre de maquina del campo de este contenido seleccionado del cual se obtendra el texto. Si selecciona "Use summary if available for selected field in post body" se usara el resumen o recortado (si esta disponible, y se existe un valor).
- Si la  opcion "Attach the link to the content" esta seleccionada, se agregara en el post un enlace al nuevo contenido.
- Seleccione la opción de publicación que quiera usar, este campo indica si se quiere "tweetear" cuando el contenido se crea.

### Configuración de acceso a la api de Twitter
> Anteriormente usted guardó el "API key", "API Secret", "Acces token" y el "Acces Token Secret".
- Ingrese el **API key**, **API Secret**, **Acces token** y el **Acces Token Secret** en este formulario, si al guardar
todo ah ido bien verá un mensaje como el siguiente:
```
Api response: Ok, @username.
```
En caso contrario se le mostrara un mensaje informado el error ocurrido. Esto le puede dar una pista de lo que
esta mal en su configuración. Estos mensajes también serán logueados en el informe de errores de drupal.
