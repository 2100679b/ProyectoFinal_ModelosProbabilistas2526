# 🎓 Proyecto Final - Modelos Probabilistas

**Universidad Michoacana de San Nicolás de Hidalgo**  
Facultad de Ingeniería Eléctrica - Ingeniería en Computación

---

## 📋 Descripción del Proyecto

Aplicación web desarrollada en **PHP puro** (sin frameworks) que implementa algoritmos fundamentales para tres tipos de modelos probabilistas:

- **Redes Bayesianas (RB)**
- **Cadenas de Markov (CM)**
- **Modelos Ocultos de Markov (HMM)**

Cada módulo incluye algoritmos de inferencia, visualización interactiva y ejemplos prácticos para demostrar su funcionamiento.

---

## 🎯 Objetivos

- Implementar algoritmos de inferencia probabilística en PHP puro
- Desarrollar una interfaz de usuario intuitiva sin frameworks
- Aplicar conceptos teóricos en problemas prácticos
- Visualizar modelos gráficos probabilistas con JavaScript vanilla

---

## ✨ Características Principales

### 🔗 Módulo de Redes Bayesianas
- ✅ Algoritmo de Enumeración para inferencia exacta
- ✅ Algoritmo de Eliminación de Variables
- ✅ Visualización gráfica de la red (JavaScript puro)
- ✅ Ejemplos: Alarma-Terremoto, Red Médica, Diagnóstico de Fallas

### ⛓️ Módulo de Cadenas de Markov
- ✅ Implementación de cadenas de primer orden
- ✅ Cálculo de matriz de transición de estados
- ✅ Cálculo de probabilidades estacionarias
- ✅ Visualización del grafo de estados
- ✅ Ejemplos: Predicción del Clima, Comportamiento del Cliente

### 🔍 Módulo de Modelos Ocultos de Markov
- ✅ Algoritmo Forward (probabilidades de observación)
- ✅ Algoritmo Viterbi (decodificación de secuencias)
- ✅ Algoritmo Forward-Backward (suavizado)
- ✅ Visualización de estados ocultos y observables
- ✅ Ejemplos: Robot y Clima, Reconocimiento de Voz

---

## 🛠️ Tecnologías Utilizadas

| Categoría | Tecnología |
|-----------|------------|
| **Backend** | PHP 7.4+ (sin frameworks) |
| **Frontend** | HTML5, CSS3 |
| **JavaScript** | Vanilla JS (sin librerías) |
| **Visualización** | Canvas API / SVG nativo |
| **Servidor** | PHP integrado / Apache / Nginx |
| **Base de datos** | No requiere |

> **Nota:** Este proyecto NO utiliza frameworks, ORMs, ni dependencias externas. Todo el código es PHP y JavaScript nativos.

---

## 📁 Estructura del Proyecto

```
ProyectoFinal_ModelosProbabilistas2526/
│
├── index.php                    # Página principal
├── config.php                   # Configuración global
├── README.md                    # Este archivo
├── .gitignore                   # Archivos ignorados por Git
│
├── assets/                      # Recursos estáticos
│   ├── css/
│   │   ├── style.css            # Estilos principales
│   │   ├── bayesian.css         # Estilos para RB
│   │   ├── markov.css           # Estilos para CM
│   │   └── hmm.css              # Estilos para HMM
│   │
│   ├── js/
│   │   ├── main.js              # JavaScript principal
│   │   ├── graph.js             # Visualización de grafos
│   │   ├── bayesian.js          # Lógica frontend RB
│   │   ├── markov.js            # Lógica frontend CM
│   │   └── hmm.js               # Lógica frontend HMM
│   │
│   └── img/                     # Imágenes e iconos
│
├── modules/                     # Módulos principales
│   ├── bayesian/
│   │   ├── index.php            # Página principal RB
│   │   ├── enumeration.php      # Algoritmo de Enumeración
│   │   ├── elimination.php      # Eliminación de Variables
│   │   ├── examples.php         # Ejemplos predefinidos
│   │   └── BayesianNetwork.php  # Clase principal
│   │
│   ├── markov/
│   │   ├── index.php            # Página principal CM
│   │   ├── transition.php       # Matriz de transición
│   │   ├── stationary.php       # Probabilidades estacionarias
│   │   ├── examples.php         # Ejemplos predefinidos
│   │   └── MarkovChain.php      # Clase principal
│   │
│   └── hmm/
│       ├── index.php            # Página principal HMM
│       ├── forward.php          # Algoritmo Forward
│       ├── viterbi.php          # Algoritmo Viterbi
│       ├── forward_backward.php # Forward-Backward
│       ├── examples.php         # Ejemplos predefinidos
│       └── HiddenMarkov.php     # Clase principal
│
├── includes/                    # Archivos comunes
│   ├── header.php               # Encabezado HTML
│   ├── footer.php               # Pie de página
│   ├── navbar.php               # Menú de navegación
│   └── functions.php            # Funciones auxiliares PHP
│
├── lib/                         # Librerías opcionales
│   └── polyfills.js             # Para navegadores antiguos
│
├── docs/                        # Documentación
│   ├── manual_usuario.pdf
│   ├── documentacion_tecnica.pdf
│   └── algoritmos.md            # Explicación de algoritmos
│
└── data/                        # Datos de ejemplo
    ├── examples_rb.json
    ├── examples_cm.json
    └── examples_hmm.json
```

---

## 🚀 Instalación y Configuración

### Requisitos Previos

- **PHP:** Versión 7.4 o superior
- **Servidor Web:** Apache, Nginx o servidor integrado de PHP
- **Navegador:** Chrome, Firefox, Edge (versiones recientes)
- **Git:** Para clonar el repositorio (opcional)

> ⚠️ **NO se requiere:** Composer, npm, MySQL, frameworks, ni dependencias externas.

---

### ⚡ Instalación Rápida (Recomendada)

#### Windows

1. **Descargar el proyecto**
```bash
# Con Git
git clone https://github.com/2100679b/ProyectoFinal_ModelosProbabilistas2526
cd ProyectoFinal_ModelosProbabilistas2526

# O descargar ZIP desde GitHub y extraer
```

2. **Iniciar servidor PHP integrado**
```bash
# Abrir CMD o PowerShell en la carpeta del proyecto
php -S localhost:8000
```

3. **Acceder al proyecto**
   - Abrir navegador
   - Ir a: `http://localhost:8000`

#### Linux/Ubuntu

1. **Verificar PHP instalado**
```bash
php --version
# Si no está instalado:
sudo apt update
sudo apt install php
```

2. **Clonar el proyecto**
```bash
cd ~
git clone https://github.com/2100679b/ProyectoFinal_ModelosProbabilistas2526
cd ProyectoFinal_ModelosProbabilistas2526
```

3. **Iniciar servidor**
```bash
php -S localhost:8000
```

4. **Acceder**
   - Ir a: `http://localhost:8000`

---

### 📦 Instalación con XAMPP (Alternativa)

#### Windows

1. **Descargar e instalar XAMPP**
   - Ir a: https://www.apachefriends.org/
   - Descargar versión para Windows
   - Instalar en `C:\xampp`

2. **Copiar el proyecto**
```bash
# Copiar carpeta completa a:
C:\xampp\htdocs\ProyectoFinal_ModelosProbabilistas2526
```

3. **Iniciar Apache**
   - Abrir XAMPP Control Panel
   - Click en "Start" junto a Apache

4. **Acceder**
   - Ir a: `http://localhost/ProyectoFinal_ModelosProbabilistas2526`

#### Linux

1. **Instalar Apache y PHP**
```bash
sudo apt update
sudo apt install apache2 php libapache2-mod-php
```

2. **Copiar el proyecto**
```bash
sudo cp -r ProyectoFinal_ModelosProbabilistas2526 /var/www/html/
```

3. **Configurar permisos**
```bash
sudo chmod -R 755 /var/www/html/ProyectoFinal_ModelosProbabilistas2526
sudo chown -R www-data:www-data /var/www/html/ProyectoFinal_ModelosProbabilistas2526
```

4. **Reiniciar Apache**
```bash
sudo systemctl restart apache2
```

5. **Acceder**
   - Ir a: `http://localhost/ProyectoFinal_ModelosProbabilistas2526`

---

## 🎮 Uso del Sistema

### Navegación Principal

1. **Página Principal** (`index.php`)
   - Presenta los tres módulos disponibles
   - Links a cada sección

2. **Seleccionar Módulo:**
   - Click en "Redes Bayesianas"
   - Click en "Cadenas de Markov"
   - Click en "Modelos Ocultos de Markov"

---

### Ejemplo: Redes Bayesianas

1. **Acceder al módulo**
   ```
   http://localhost:8000/modules/bayesian/
   ```

2. **Cargar ejemplo predefinido**
   - Seleccionar "Alarma-Terremoto" del menú desplegable
   - Click en "Cargar Ejemplo"

3. **Configurar nodos**
   - Definir variables de la red
   - Establecer dependencias
   - Ingresar tablas de probabilidad condicional

4. **Ejecutar inferencia**
   - Seleccionar algoritmo (Enumeración o Eliminación)
   - Especificar evidencia
   - Click en "Calcular"

5. **Ver resultados**
   - Probabilidades posteriores
   - Visualización gráfica de la red
   - Pasos del algoritmo

---

### Ejemplo: Cadenas de Markov

1. **Acceder al módulo**
   ```
   http://localhost:8000/modules/markov/
   ```

2. **Definir estados**
   - Ingresar estados del sistema (ej: Soleado, Lluvioso)

3. **Configurar matriz de transición**

4. **Calcular**
   - Probabilidades de transición
   - Distribución estacionaria
   - Predicciones futuras

5. **Visualizar**
   - Grafo de estados y transiciones
   - Matriz en formato tabla

---

### Ejemplo: HMM

1. **Acceder al módulo**
   ```
   http://localhost:8000/modules/hmm/
   ```

2. **Configurar modelo**
   - Estados ocultos (ej: Clima real)
   - Observaciones (ej: Sensores del robot)
   - Probabilidades de emisión

3. **Ejecutar algoritmo**
   - Forward: Probabilidad de secuencia
   - Viterbi: Secuencia de estados más probable
   - Forward-Backward: Suavizado

4. **Analizar resultados**
   - Secuencia decodificada
   - Probabilidades por estado
   - Visualización temporal

---

## 🧪 Ejemplos Predefinidos

### Redes Bayesianas

| Ejemplo | Descripción | Nodos | Dificultad |
|---------|-------------|-------|------------|
| Alarma-Terremoto | Red clásica con 5 nodos | 5 | Básica |
| Red Médica | Diagnóstico de enfermedades | 8 | Media |
| Diagnóstico de Fallas | Detección de problemas | 10 | Media |
| Clima Complejo | Predicción meteorológica | 12 | Avanzada |

### Cadenas de Markov

| Ejemplo | Descripción | Estados | Tipo |
|---------|-------------|---------|------|
| Predicción del Clima | Soleado, Nublado, Lluvioso | 3 | Básico |
| Comportamiento del Cliente | Nuevo, Activo, Inactivo | 3 | Medio |
| Análisis de Texto | Estados de palabras | 5 | Avanzado |

### HMM

| Ejemplo | Descripción | Estados Ocultos | Observaciones |
|---------|-------------|-----------------|---------------|
| Robot y Clima | 2 (Soleado, Lluvioso) | 3 (Sensores) | Básico |
| Reconocimiento de Voz | 4 (Fonemas) | 6 (Señales) | Medio |
| Análisis de ADN | 3 (Regiones) | 4 (Bases) | Avanzado |

---

## 🔧 Configuración Avanzada

### Editar `config.php`

```php
<?php
// Configuración global del proyecto

// Rutas base
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost:8000');

// Límites para redes bayesianas
define('MIN_NODES', 3);
define('MAX_NODES', 20);

// Límites para cadenas de Markov
define('MIN_STATES', 2);
define('MAX_STATES', 15);

// Límites para HMM
define('MIN_HIDDEN_STATES', 2);
define('MAX_HIDDEN_STATES', 10);

// Precisión numérica
define('PROBABILITY_DECIMALS', 6);
define('FLOAT_PRECISION', 1e-10);

// Debug mode
define('DEBUG_MODE', false);

// Configuración de sesión
ini_set('session.cookie_lifetime', 3600); // 1 hora
session_start();

// Función de autoload simple (sin Composer)
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/modules/' . strtolower($class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
?>
```

---

## 🐛 Solución de Problemas

### Problema: "Cannot access localhost:8000"

**Causa:** Puerto ocupado o servidor no iniciado

**Solución:**
```bash
# Cambiar puerto
php -S localhost:8080

# Verificar si algo usa el puerto
netstat -ano | findstr :8000  # Windows
lsof -i :8000                  # Linux
```

---

### Problema: "Call to undefined function..."

**Causa:** `config.php` no incluido

**Solución:** Verificar que cada archivo PHP tenga:
```php
<?php
require_once __DIR__ . '/../../config.php';
?>
```

---

### Problema: CSS/JS no se cargan

**Causa:** Rutas incorrectas

**Solución:**
```php
// En header.php usar rutas absolutas desde BASE_URL
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
```

---

### Problema: "Undefined variable" en PHP

**Causa:** Variables no inicializadas

**Solución:** Activar `display_errors` en desarrollo:
```php
// Agregar al inicio de config.php
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

---

### Problema: Errores de permisos (Linux)

**Solución:**
```bash
# Dar permisos de lectura/escritura
chmod -R 755 ProyectoFinal_ModelosProbabilistas2526

# Si usa Apache
sudo chown -R www-data:www-data ProyectoFinal_ModelosProbabilistas2526
```

---

### Problema: "Cannot modify header information"

**Causa:** Salida antes de `header()`

**Solución:**
- No dejar espacios antes de `<?php`
- Usar `ob_start()` al inicio si es necesario
- Verificar que no haya `echo` antes de redirecciones

---

## 📚 Estructura del Código

### Ejemplo: Clase `BayesianNetwork`

```php
<?php
// modules/bayesian/BayesianNetwork.php

class BayesianNetwork {
    private $nodes = [];
    private $edges = [];
    private $cpt = []; // Conditional Probability Tables
    
    public function addNode($name) {
        $this->nodes[] = $name;
    }
    
    public function addEdge($from, $to) {
        $this->edges[] = ['from' => $from, 'to' => $to];
    }
    
    public function setCPT($node, $table) {
        $this->cpt[$node] = $table;
    }
    
    public function enumerate($query, $evidence = []) {
        // Implementación del algoritmo de enumeración
        // ...
        return $result;
    }
    
    public function eliminateVariables($query, $evidence = []) {
        // Implementación de eliminación de variables
        // ...
        return $result;
    }
}
?>
```

### Ejemplo: Uso en página

```php
<?php
// modules/bayesian/index.php
require_once '../../config.php';
require_once 'BayesianNetwork.php';

// Crear red
$bn = new BayesianNetwork();
$bn->addNode('Alarma');
$bn->addNode('Terremoto');
$bn->addEdge('Terremoto', 'Alarma');

// Calcular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = $_POST['query'] ?? '';
    $evidence = $_POST['evidence'] ?? [];
    
    $result = $bn->enumerate($query, $evidence);
    
    // Devolver JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

include '../../includes/header.php';
?>

<div class="container">
    <h1>Redes Bayesianas</h1>
    <form id="bayesian-form" method="POST">
        <!-- Formulario -->
    </form>
    <div id="result"></div>
    <canvas id="network-graph"></canvas>
</div>

<script src="<?= BASE_URL ?>/assets/js/bayesian.js"></script>

<?php include '../../includes/footer.php'; ?>
```

---

## 👥 Equipo de Desarrollo

**Estudiante:**
- Abraham Flores Ávila
- Correo: 2100679b@umich.mx

**Profesor:**
- Mauricio Reyes
- Correo: mauricio.reyes@umich.mx

---

## 📅 Cronograma

| Fecha | Entrega |
|-------|---------|
| Nov 27, 2025 | Inicio del proyecto |
| Dic 10, 2025 | Avance 1 (Redes Bayesianas) |
| Dic 17, 2025 | Documentación completa (12:00 PM) |
| Dic 19, 2025 | Presentación final (10:00-13:00 hrs) |

---

## 📝 Licencia

Este proyecto es parte del curso de Modelos Probabilistas de la Universidad Michoacana de San Nicolás de Hidalgo.

© 2025 UMSNH - Facultad de Ingeniería Eléctrica

---

## 🔗 Referencias y Recursos

### Documentación PHP
- [PHP Manual](https://www.php.net/manual/es/)
- [PHP Arrays](https://www.php.net/manual/es/language.types.array.php)
- [PHP OOP](https://www.php.net/manual/es/language.oop5.php)

### Algoritmos
- Russell & Norvig - *Artificial Intelligence: A Modern Approach*
- Daphne Koller - *Probabilistic Graphical Models*

### JavaScript Vanilla
- [MDN Web Docs](https://developer.mozilla.org/)
- [Canvas API](https://developer.mozilla.org/es/docs/Web/API/Canvas_API)

### Git
- [Git Documentation](https://git-scm.com/doc)
- [GitHub Guides](https://guides.github.com/)

---

## 📞 Contacto y Soporte

**Para dudas sobre el proyecto:**

- **Correo:** mauricio.reyes@umich.mx
- **Ubicación:** Laboratorio de Simulación y Cómputo Avanzado  
  Edificio "B" Planta Alta  
  Facultad de Ingeniería Eléctrica, UMSNH
- **Horario de atención:** Lunes a Viernes, 10:00 - 14:00 hrs

---

## 🚀 Comandos Útiles

```bash
# Iniciar servidor PHP integrado
php -S localhost:8000

# Iniciar en otro puerto
php -S localhost:8080

# Ver versión de PHP
php --version

# Verificar sintaxis de un archivo
php -l archivo.php

# Ejecutar script de prueba
php tests/test_bayesian.php
```

---

## 📋 Checklist de Entrega

- [ ] Código fuente completo
- [ ] README.md actualizado
- [ ] Manual de usuario (PDF)
- [ ] Documentación técnica (PDF)
- [ ] Ejemplos funcionando
- [ ] Sin errores PHP
- [ ] Probado en al menos 2 navegadores
- [ ] Código comentado
- [ ] Estructura de carpetas organizada

---

**Última actualización:** Diciembre 2025  
**Versión:** 1.0.0