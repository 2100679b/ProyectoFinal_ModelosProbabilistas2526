@echo off
echo Creando estructura del proyecto...

mkdir assets\css
mkdir assets\js
mkdir assets\img\ejemplos
mkdir modules\bayesian\algorithms
mkdir modules\bayesian\examples
mkdir modules\markov\algorithms
mkdir modules\markov\examples
mkdir modules\hmm\algorithms
mkdir modules\hmm\examples
mkdir includes
mkdir lib\vis-network
mkdir lib\chart.js
mkdir docs\diagramas
mkdir tests

echo Creando archivos principales...

type nul > index.php
type nul > config.php
type nul > README.md
type nul > .gitignore
type nul > assets\css\style.css
type nul > assets\css\bayesian.css
type nul > assets\css\markov.css
type nul > assets\css\hmm.css
type nul > assets\js\main.js
type nul > assets\js\visualization.js
type nul > assets\js\bayesian.js
type nul > assets\js\markov.js
type nul > assets\js\hmm.js

type nul > modules\bayesian\index.php
type nul > modules\bayesian\network.php
type nul > modules\bayesian\visualization.php
type nul > modules\bayesian\algorithms\enumeration.php
type nul > modules\bayesian\algorithms\variable_elimination.php

type nul > modules\markov\index.php
type nul > modules\markov\chain.php
type nul > modules\markov\visualization.php
type nul > modules\markov\algorithms\first_order.php
type nul > modules\markov\algorithms\transition_matrix.php
type nul > modules\markov\algorithms\stationary.php

type nul > modules\hmm\index.php
type nul > modules\hmm\model.php
type nul > modules\hmm\visualization.php
type nul > modules\hmm\algorithms\forward.php
type nul > modules\hmm\algorithms\viterbi.php
type nul > modules\hmm\algorithms\forward_backward.php

type nul > includes\header.php
type nul > includes\footer.php
type nul > includes\navbar.php
type nul > includes\functions.php

echo.
echo Estructura creada exitosamente!
pause