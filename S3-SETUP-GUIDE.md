# Configuración de S3 para Comprobantes Yape

## 1. Configuración del Bucket S3

### Crear Bucket en AWS Console:
1. Ve a la consola de AWS S3
2. Crea un nuevo bucket con nombre: `financiera-prisma-storage`
3. Región: `us-east-2`
4. Deja las opciones por defecto

### Configurar Política del Bucket:
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::financiera-prisma-storage/*"
        }
    ]
}
```

### Configurar CORS:
```json
[
    {
        "AllowedHeaders": [
            "*"
        ],
        "AllowedMethods": [
            "GET",
            "HEAD"
        ],
        "AllowedOrigins": [
            "*"
        ],
        "ExposeHeaders": []
    }
]
```

## 2. Configuración de Variables de Entorno

### En desarrollo (.env local):
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=tu_access_key
AWS_SECRET_ACCESS_KEY=tu_secret_key
AWS_DEFAULT_REGION=us-east-2
AWS_BUCKET=financiera-prisma-storage
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### En producción (Sevalla/Kinsta):
Configura estas variables en el panel de control del hosting:
- `FILESYSTEM_DISK=s3`
- `AWS_ACCESS_KEY_ID=tu_access_key`
- `AWS_SECRET_ACCESS_KEY=tu_secret_key`
- `AWS_DEFAULT_REGION=us-east-2`
- `AWS_BUCKET=financiera-prisma-storage`
- `AWS_USE_PATH_STYLE_ENDPOINT=false`

## 3. Estructura de Carpetas en S3

Los comprobantes se guardarán en:
- `yape-comprobantes/` - Para comprobantes de préstamos
- `yape-comprobantes-ahorros/` - Para comprobantes de ahorros
- `yape-comprobantes-cierre-caja/` - Para comprobantes de cierre de caja

## 4. URLs Generadas

Las URLs tendrán el formato:
```
https://financiera-prisma-storage.s3.us-east-2.amazonaws.com/yape-comprobantes/nombre_archivo.jpg
```

## 5. Controladores Actualizados

✅ CollectionController - Préstamos y Ahorros
✅ ReportController - Cierre de Caja

## 6. S3Service

Se ha creado un servicio helper en `app/Services/S3Service.php` con métodos:
- `uploadImage()` - Subir imágenes a S3
- `deleteImage()` - Eliminar imágenes de S3
- `imageExists()` - Verificar si existe una imagen

## 7. Seguridad

- Las credenciales de AWS están configuradas como variables de entorno
- El bucket tiene política de lectura pública para las imágenes
- Los nombres de archivo son únicos (timestamp + uniqid)
- Solo se permiten imágenes (jpeg, jpg, png) hasta 5MB
