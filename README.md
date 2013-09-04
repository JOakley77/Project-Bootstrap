# Project Bootstrap
Just another bootstrap for rapid project development. Not too much to this but it has some  advantages. This is more for personal use then anything so if anyone gets some use out of it please let me know and I'll do my best to keep the project updated.

---

## GruntJS
A set of Grunt tasks for watching project files. It beeps.

## Sass/Compass
My proposed Compass project structure when I work on projects. It helps keep me organized.

## CodeIgniter > `2.1.4`

###Included Libraries

* [HMVC](https://bitbucket.org/wiredesignz/codeigniter-modular-extensions-hmvc)
* [Cache](https://github.com/philsturgeon/codeigniter-cache)
* [Template](https://github.com/philsturgeon/codeigniter-template)
* [REST Server](https://github.com/philsturgeon/codeigniter-restserver)

###Customizations

| What/Huh? | Description |
| ------------- | ------------- |
| config/MY_config.php    | project specific configuration    |
| core/MY_Controller.php    | parent class for all other modules to extend from (more info below)    |
| core/Widgets_Controller.php | parent class for widgets |
| helpers/app_helper.php | small site helpers (more info below) |
| modules/home/*	| default module |
| modules/home/widget/* |	example widget |
| modules/search/* | small site search project I've been fiddeling with |
| modules/widgets/*	| widgets module |
| views/layouts/* 	| default layout |
| views/partials/*	| default partials for heading/footer/etc |

### Explanations
#### MY_Controller.php
Not much to this. Here is where a majority of the application specific configuration happens. You will see some specific logic for configurating the template as well as several custom methods.

`get_active_page()`: get's the current active page based on the URL segment.

`object_to_array()`: recursively converts an object to an array

`array_to_object()`: recursively converts an array to an object (FYI but CI's parser hates objects so you have to wrap these in an array otherwise you get some nasty errors).

#### app_helper.php
Currently only one function in here.

`set_active()`: perform some action when some criteria is met. __Usage__: set_active( 'page', $active_segment, 'class="active"' )

#### Example Widget
The example is probably good enough to illustrate how to create/use a widget. You can add them to any module within the project.

#### Search Module
This is one of those "work in progress" type things. It's based on Zend_Lucene because I keep getting clients do not host on anything "enjoyable" which limits what I can install on the server. This is self contained. 

I ripped it out of another project I was working on so it's only partially functional at the moment (actually, it's 100% functional it just doesn't do much in here). For a breakdown of how it works you can refer to the Search library within the module and see what you can do!

#### .htaccess
Default HTML5 Boilerplate .htaccess file with the CodeIgniter rewrite functionality added in.