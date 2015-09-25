Site Core
===================


Site core is a extended version of Code igniter 3.0. It was designed to suite 
the needs of any CI developer who wants a full modulable version of this framework.
 Although it is almost fully usable in the regular Code igniter 3.0 way, it comes
 with fully preinstalled [HMVC][9], [Spark][10] and [Composer][11] supports so you
 don't have to install them yourself. Moreover, it includes a *module manager* 
(which is **highly** inspired from Spark) to allow creation of clean extendables 
HMVC modules that can depend on other composer, spark or other HMVC module.

----------


Composer
-------------

As you may know, Composer is a very powerful and popular tool to help you getting, 
creating or sharing php packages in a clean and efficient way. In the site-core, 
you can use composer to install packages it as follows :

    php tools/composer --working-dir=application/ install

> **Note:**

> Of course, this requires you defined all your Composer dependencies in the composer.json 
file that located in the application folder. See [composer documentation][12] for details.

Spark
-------

Spark in an other tool which is aimed to provide a simple but robust way to share 
Code Igniter piece of code, named sparks. Although it has been a bit outdated by composer, 
it has the advantage of having been designed for CI and thus it works just fine with 
this framework and is very easy to use. Moreover, it still contains very handy piece 
of code indeed. To install the version X.X.X of a spark, just tape the following in the prompt :

    php tools/spark install -vX.X.X [myspark]

HMVC Module
------------------

The core provides a spark-like cmd line tool to help you install, create and share 
full HMVC modules. HMVC modules are a bit similar to sparks excepting they can also 
contain CI controllers and views. Custom SQL, JS and CSS files are supported as well 
by the core. The way you'd install module is very similar to the spark one :

    php tools/module install -vX.X.X [mymodule]


  [1]: http://math.stackexchange.com/
  [2]: http://daringfireball.net/projects/markdown/syntax "Markdown"
  [3]: https://github.com/jmcmanus/pagedown-extra "Pagedown Extra"
  [4]: http://meta.math.stackexchange.com/questions/5020/mathjax-basic-tutorial-and-quick-reference
  [5]: https://code.google.com/p/google-code-prettify/
  [6]: http://highlightjs.org/
  [7]: http://bramp.github.io/js-sequence-diagrams/
  [8]: http://adrai.github.io/flowchart.js/
  [9]: https://bitbucket.org/wiredesignz/codeigniter-modular-extensions-hmvc
  [10]: http://getsparks.org/
  [11]: https://getcomposer.org/
  [12]: https://getcomposer.org/doc/04-schema.md#the-composer-json-schema


