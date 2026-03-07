# Imágenes del banner hero (igual que contrive)

Para que el hero de la portada se vea **exactamente** como el template contrive (`design-goal_envato_contrive/index.html`, sección banner 161–279), necesitas estas **5 imágenes** en el tema.

## Dónde están en contrive

Copia desde:

`design-goal_envato_contrive/assets/images/`

## Dónde ponerlas en el tema bakka

Crea la carpeta si no existe y copia los archivos ahí:

`assets/images/`

(raíz del tema bakka, mismo nivel que `src/`, `build/`, `functions.php`.)

## Lista de archivos

| Archivo en contrive | Uso en el hero |
|---------------------|----------------|
| **banner-backgroundimage.jpg** | Fondo de toda la sección (cover, centrado). |
| **update-leftimage.png** | Imagen decorativa izquierda (abajo a la izquierda). En responsive &lt; 1440px se muestra más pequeña (~70px). |
| **about-rightimage.png** | Imagen decorativa derecha (arriba a la derecha). En responsive &lt; 1440px ~70px. |
| **banner-image.png** | Imagen principal del hero (mueble/producto) con el círculo blanco detrás. Si no configuras imagen en el bloque, el tema usará esta por defecto. |
| **banner-arrow.png** | Flecha decorativa junto a la imagen principal. |

## Después de copiar

1. Copia los 5 archivos a `bakka/assets/images/`.
2. En la portada, el hero usará:
   - `banner-backgroundimage.jpg` como fondo.
   - `update-leftimage.png` y `about-rightimage.png` como decorativas.
   - La imagen del bloque (Medios) si tienes una asignada; si no, `banner-image.png`.
   - `banner-arrow.png` como flecha.

Si falta algún archivo, esa parte se oculta (fondo con gradiente por defecto, decorativas y flecha no se muestran).
