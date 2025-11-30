# 🎓 Proyecto Final - Modelos Probabilistas

## Universidad Michoacana de San Nicolás de Hidalgo
### Facultad de Ingeniería Eléctrica - Ingeniería en Computación

---

## 📋 Descripción del Proyecto

Aplicación web desarrollada en PHP que implementa algoritmos fundamentales para tres tipos de modelos probabilistas:

- **Redes Bayesianas (RB)**
- **Cadenas de Markov (CM)**
- **Modelos Ocultos de Markov (HMM)**

Cada módulo incluye algoritmos de inferencia, visualización interactiva y ejemplos prácticos para demostrar su funcionamiento.

---

## 🎯 Objetivos

- Implementar algoritmos de inferencia probabilística
- Desarrollar una interfaz de usuario intuitiva
- Aplicar conceptos teóricos en problemas prácticos
- Visualizar modelos gráficos probabilistas

---

## ✨ Características Principales

### 🔗 Módulo de Redes Bayesianas
- ✅ Algoritmo de Enumeración para inferencia exacta
- ✅ Algoritmo de Eliminación de Variables
- ✅ Visualización gráfica de la red
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

- **Backend**: PHP 7.4+
- **Frontend**: HTML5, CSS3, JavaScript
- **Visualización**: Vis.js / D3.js
- **Servidor**: Apache / Nginx
- **Control de versiones**: Git

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
│   ├── css/                     # Hojas de estilo
│   ├── js/                      # Scripts JavaScript
│   └── img/                     # Imágenes
│
├── modules/                     # Módulos principales
│   ├── bayesian/                # Redes Bayesianas
│   ├── markov/                  # Cadenas de Markov
│   └── hmm/                     # Modelos Ocultos de Markov
│
├── includes/                    # Archivos comunes
│   ├── header.php               # Encabezado HTML
│   ├── footer.php               # Pie de página
│   ├── navbar.php               # Menú de navegación
│   └── functions.php            # Funciones auxiliares
│
├── lib/                         # Librerías externas
│   └── vis-network/             # Visualización de grafos
│
├── docs/                        # Documentación
│   ├── manual_usuario.pdf       # Manual de usuario
│   └── documentacion_tecnica.pdf # Documentación técnica
│
└── tests/                       # Pruebas (opcional)
```

---

## 🚀 Instalación y Configuración

### Requisitos Previos

- **PHP**: Versión 7.4 o superior
- **Servidor Web**: Apache, Nginx o servidor integrado de PHP
- **Navegador**: Chrome, Firefox, Edge (versiones recientes)
- **Git**: Para clonar el repositorio (opcional)

### Instalación en Windows

#### Opción 1: Con XAMPP

1. **Descargar e instalar XAMPP**
   - Ir a: https://www.apachefriends.org/
   - Descargar e instalar

2. **Clonar o copiar el proyecto**
   ```bash
   cd C:\xampp\htdocs
   git clone [URL_DEL_REPOSITORIO] ProyectoFinal_ModelosProbabilistas2526
   ```
   O simplemente copiar la carpeta del proyecto a `C:\xampp\htdocs\`

3. **Iniciar Apache**
   - Abrir XAMPP Control Panel
   - Click en "Start" en Apache

4. **Acceder al proyecto**
   - Abrir navegador
   - Ir a: `http://localhost/ProyectoFinal_ModelosProbabilistas2526`

#### Opción 2: Con PHP integrado

1. **Abrir terminal en la carpeta del proyecto**
   ```bash
   cd F:\Users\Lenovo L430\Desktop\ProyectoFinal_ModelosProbabilistas2526
   ```

2. **Iniciar servidor PHP**
   ```bash
   php -S localhost:8000
   ```

3. **Acceder al proyecto**
   - Ir a: `http://localhost:8000`

### Instalación en Linux/Ubuntu

1. **Instalar Apache y PHP**
   ```bash
   sudo apt update
   sudo apt install apache2 php libapache2-mod-php
   ```

2. **Clonar el proyecto**
   ```bash
   cd /var/www/html
   sudo git clone [URL_DEL_REPOSITORIO] ProyectoFinal_ModelosProbabilistas2526
   ```

3. **Asignar permisos**
   ```bash
   sudo chmod -R 755 ProyectoFinal_ModelosProbabilistas2526
   sudo chown -R www-data:www-data ProyectoFinal_ModelosProbabilistas2526
   ```

4. **Reiniciar Apache**
   ```bash
   sudo systemctl restart apache2
   ```

5. **Acceder al proyecto**
   - Ir a: `http://localhost/ProyectoFinal_ModelosProbabilistas2526`

---

## 📖 Uso del Sistema

### Navegación Principal

1. **Acceder a la página principal** (`index.php`)
2. **Seleccionar un módulo**:
   - Redes Bayesianas
   - Cadenas de Markov
   - Modelos Ocultos de Markov

### Ejemplo: Usar Redes Bayesianas

1. Click en "Redes Bayesianas"
2. Seleccionar un ejemplo pre-cargado o crear una red nueva
3. Configurar:
   - Nodos de la red
   - Dependencias entre nodos
   - Tablas de probabilidad condicional
4. Ejecutar algoritmo de inferencia
5. Visualizar resultados

---

## 🧪 Ejemplos Incluidos

### Redes Bayesianas
- **Alarma-Terremoto-Ladrón**: Ejemplo clásico de red bayesiana
- **Red Médica**: Diagnóstico de enfermedades basado en síntomas
- **Diagnóstico de Fallas**: Detección de problemas en sistemas
- **Predicción Climática**: Modelado de condiciones meteorológicas

### Cadenas de Markov
- **Predicción del Clima**: Transiciones entre estados climáticos
- **Comportamiento del Cliente**: Análisis de patrones de compra

### HMM
- **Robot y Clima**: Robot que infiere el clima desde observaciones
- **Reconocimiento de Voz**: Secuencias de fonemas ocultos

---

## 🔧 Configuración Avanzada

### Modificar límites del sistema

Editar `config.php`:

```php
// Límites para redes bayesianas
define('MIN_NODES', 5);
define('MAX_NODES', 15);

// Límites para cadenas de Markov
define('MIN_STATES', 2);
define('MAX_STATES', 10);
```

### Cambiar precisión numérica

```php
// Decimales para probabilidades
define('PROBABILITY_DECIMALS', 6);
```

---

## 🐛 Solución de Problemas

### Problema: "Call to undefined function..."
**Solución**: Verificar que `config.php` esté incluido en todos los archivos PHP.

### Problema: CSS/JS no se cargan
**Solución**: 
1. Verificar que la carpeta `assets/` exista
2. Revisar permisos (Linux: `chmod -R 755 assets/`)

### Problema: Errores de ruta en Linux
**Solución**: El proyecto usa rutas relativas automáticas. Verificar que `config.php` no tenga modificaciones manuales.

### Problema: "Cannot modify header information"
**Solución**: Asegurarse de que no haya espacios o saltos de línea antes de `<?php` en archivos PHP.

---

## 📚 Documentación Adicional

- **Manual de Usuario**: Ver `docs/manual_usuario.pdf`
- **Documentación Técnica**: Ver `docs/documentacion_tecnica.pdf`
- **Ejemplos de Uso**: Ver `docs/ejemplos_uso.md`

---

## 👥 Equipo de Desarrollo

- **Estudiante 1**: [Nombre completo]
- **Estudiante 2**: [Nombre completo]

**Profesor**: Mauricio Reyes  
**Correo**: mauricio.reyes@umich.mx

---

## 📅 Fechas Importantes

- **Fecha de inicio**: Noviembre 27, 2025
- **Última entrega de documentación**: Diciembre 17, 2025 - 12:00 PM
- **Última fecha de presentación**: Diciembre 19, 2025 - 10:00-13:00 hrs

---

## 📝 Licencia

Este proyecto es parte del curso de Modelos Probabilistas de la Universidad Michoacana de San Nicolás de Hidalgo.

© 2025 UMSNH - Facultad de Ingeniería Eléctrica

---

## 🔗 Enlaces Útiles

- [PHP Documentation](https://www.php.net/docs.php)
- [Vis.js Network](https://visjs.github.io/vis-network/docs/network/)
- [Git Documentation](https://git-scm.com/doc)

---

## 📞 Contacto y Soporte

Para dudas sobre el proyecto:
- **Correo**: mauricio.reyes@umich.mx
- **Ubicación**: Laboratorio de Simulación y Cómputo Avanzado, Edificio "B" Planta Alta

---

**Última actualización**: Noviembre 2025