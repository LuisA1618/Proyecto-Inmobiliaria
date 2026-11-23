-- ============================================================================
-- SISTEMA WEB DE GESTIÓN INMOBILIARIA - INMOBILIARIA ALTAVISTA
-- Base de Datos MySQL (Compatible con XAMPP / MariaDB / MySQL 5.7+ y 8.0+)
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "-04:00";

CREATE DATABASE IF NOT EXISTS `inmobiliaria_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `inmobiliaria_db`;

DROP TABLE IF EXISTS `consultas_clientes`;
DROP TABLE IF EXISTS `publicaciones`;
DROP TABLE IF EXISTS `propiedades`;
DROP TABLE IF EXISTS `propietarios`;
DROP TABLE IF EXISTS `agentes`;

CREATE TABLE `agentes` (
  `id_agente` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `telefono` VARCHAR(30) NOT NULL,
  `whatsapp` VARCHAR(30) DEFAULT NULL,
  `cargo` VARCHAR(80) NOT NULL DEFAULT 'Asesor Inmobiliario',
  `password_hash` VARCHAR(255) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_agente`),
  UNIQUE KEY `uk_agentes_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `propietarios` (
  `id_propietario` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `documento_identidad` VARCHAR(30) NOT NULL,
  `telefono` VARCHAR(30) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `direccion_residencia` VARCHAR(255) DEFAULT NULL,
  `notas` TEXT DEFAULT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_propietario`),
  UNIQUE KEY `uk_propietarios_documento` (`documento_identidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `propiedades` (
  `id_propiedad` INT NOT NULL AUTO_INCREMENT,
  `codigo_referencia` VARCHAR(20) NOT NULL,
  `titulo` VARCHAR(180) NOT NULL,
  `tipo_inmueble` ENUM('Casa', 'Departamento', 'Terreno', 'Local Comercial', 'Oficina', 'Quinta') NOT NULL,
  `tipo_operacion` ENUM('Venta', 'Alquiler', 'Anticrético') NOT NULL DEFAULT 'Venta',
  `direccion` VARCHAR(255) NOT NULL,
  `zona_ciudad` VARCHAR(120) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `precio` DECIMAL(12,2) NOT NULL,
  `moneda` ENUM('USD', 'BOB') NOT NULL DEFAULT 'USD',
  `habitaciones` INT NOT NULL DEFAULT 0,
  `banos` INT NOT NULL DEFAULT 0,
  `superficie_m2` DECIMAL(10,2) NOT NULL,
  `estacionamientos` INT NOT NULL DEFAULT 0,
  `imagen_principal` VARCHAR(255) NOT NULL DEFAULT 'no-image.jpg',
  `estado_registro` ENUM('Registrada', 'Publicada', 'No Publicada') NOT NULL DEFAULT 'Registrada',
  `id_propietario` INT NOT NULL,
  `id_agente` INT NOT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_propiedad`),
  UNIQUE KEY `uk_propiedades_codigo` (`codigo_referencia`),
  KEY `idx_propiedades_propietario` (`id_propietario`),
  KEY `idx_propiedades_agente` (`id_agente`),
  KEY `idx_propiedades_estado` (`estado_registro`),
  CONSTRAINT `fk_propiedades_propietario`
    FOREIGN KEY (`id_propietario`) REFERENCES `propietarios` (`id_propietario`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_propiedades_agente`
    FOREIGN KEY (`id_agente`) REFERENCES `agentes` (`id_agente`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `publicaciones` (
  `id_publicacion` INT NOT NULL AUTO_INCREMENT,
  `id_propiedad` INT NOT NULL,
  `id_agente_publicador` INT NOT NULL,
  `estado_publicacion` ENUM('Publicada', 'No Publicada') NOT NULL DEFAULT 'Publicada',
  `destacada` TINYINT(1) NOT NULL DEFAULT 0,
  `visitas` INT NOT NULL DEFAULT 0,
  `fecha_publicacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_retiro` DATETIME DEFAULT NULL,
  `observaciones` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_publicacion`),
  UNIQUE KEY `uk_publicaciones_propiedad` (`id_propiedad`),
  KEY `idx_publicaciones_estado` (`estado_publicacion`),
  KEY `idx_publicaciones_agente` (`id_agente_publicador`),
  CONSTRAINT `fk_publicaciones_propiedad`
    FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id_propiedad`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_publicaciones_agente`
    FOREIGN KEY (`id_agente_publicador`) REFERENCES `agentes` (`id_agente`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `consultas_clientes` (
  `id_consulta` INT NOT NULL AUTO_INCREMENT,
  `id_propiedad` INT NOT NULL,
  `id_agente` INT NOT NULL,
  `nombre_cliente` VARCHAR(120) NOT NULL,
  `email_cliente` VARCHAR(150) NOT NULL,
  `telefono_cliente` VARCHAR(30) NOT NULL,
  `mensaje` TEXT NOT NULL,
  `estado` ENUM('Pendiente', 'Contactado') NOT NULL DEFAULT 'Pendiente',
  `fecha_consulta` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_consulta`),
  KEY `idx_consultas_propiedad` (`id_propiedad`),
  KEY `idx_consultas_agente` (`id_agente`),
  CONSTRAINT `fk_consultas_propiedad`
    FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id_propiedad`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_consultas_agente`
    FOREIGN KEY (`id_agente`) REFERENCES `agentes` (`id_agente`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de prueba (Contraseña de agentes: Admin123*)
INSERT INTO `agentes` (`id_agente`, `nombre`, `apellido`, `email`, `telefono`, `whatsapp`, `cargo`, `password_hash`, `activo`) VALUES
(1, 'Carlos', 'Mendoza', 'carlos.mendoza@altavista.com', '+591 71045821', '59171045821', 'Director Comercial', '$2y$12$Hs.JtaIsDDqwiQYni3wvKuV6/.PZ9Ta./5SrH1MDkw7asgBYMuRNy', 1),
(2, 'Valeria', 'Rojas', 'valeria.rojas@altavista.com', '+591 72189340', '59172189340', 'Asesora Inmobiliaria Senior', '$2y$12$Hs.JtaIsDDqwiQYni3wvKuV6/.PZ9Ta./5SrH1MDkw7asgBYMuRNy', 1),
(3, 'Diego', 'Fernández', 'diego.fernandez@altavista.com', '+591 76320915', '59176320915', 'Especialista Corporativo y Terrenos', '$2y$12$Hs.JtaIsDDqwiQYni3wvKuV6/.PZ9Ta./5SrH1MDkw7asgBYMuRNy', 1);

INSERT INTO `propietarios` (`id_propietario`, `nombre`, `apellido`, `documento_identidad`, `telefono`, `email`, `direccion_residencia`, `notas`) VALUES
(1, 'Roberto', 'Salazar', '4829104 SC', '+591 70811223', 'roberto.salazar@correo.com', 'Av. San Martín #450, Equipetrol', 'Propietario de dos inmuebles residenciales en zona norte.'),
(2, 'María Elena', 'Guzmán', '3910482 SC', '+591 70944556', 'mguzman@correo.com', 'Calle Los Gomeros #112, Sirari', 'Prefiere contacto telefónico por la tarde.'),
(3, 'Fernando', 'Quiroga', '5192837 LP', '+591 71566778', 'fquiroga@correo.com', 'Av. Beni 3er Anillo Interno #890', 'Inversionista comercial con disponibilidad inmediata de entrega.'),
(4, 'Lucía', 'Torrico', '6283910 CB', '+591 72688990', 'lucia.torrico@correo.com', 'Barrio Urbarí, Calle Tucumán #74', 'Documentación al día y lista para firma.'),
(5, 'Jorge Luis', 'Paniagua', '4471920 SC', '+591 73100234', 'jpaniagua@correo.com', 'Condominio La Hacienda, Urubó', 'Interesado en venta directa o financiamiento bancario.');

INSERT INTO `propiedades` (`id_propiedad`, `codigo_referencia`, `titulo`, `tipo_inmueble`, `tipo_operacion`, `direccion`, `zona_ciudad`, `descripcion`, `precio`, `moneda`, `habitaciones`, `banos`, `superficie_m2`, `estacionamientos`, `imagen_principal`, `estado_registro`, `id_propietario`, `id_agente`) VALUES
(1, 'PROP-001', 'Residencia Moderna con Piscina en Equipetrol Norte', 'Casa', 'Venta', 'Calle 8 Este #245, Equipetrol Norte', 'Equipetrol, Santa Cruz', 'Espectacular residencia de dos plantas con diseño arquitectónico contemporáneo, amplios ventanales de piso a techo, piscina privada temperada, churrasquera techada, cocina equipada con mesones de cuarzo, suite principal con vestidor y seguridad 24/7.', 345000.00, 'USD', 4, 4, 380.00, 2, 'prop_1.jpg', 'Publicada', 1, 1),
(2, 'PROP-002', 'Departamento Panorámico en Torre Mirador', 'Departamento', 'Venta', 'Av. Cristóbal de Mendoza #1120, Piso 11', 'Zona Norte, Santa Cruz', 'Luminoso departamento con vista despejada a la ciudad. Cuenta con living-comedor integrado, balcón amplio, cocina americana amoblada, dormitorio en suite, dos dormitorios auxiliares y acceso a áreas sociales con piscina en terraza y gimnasio.', 168000.00, 'USD', 3, 2, 145.00, 2, 'prop_2.jpg', 'Publicada', 2, 2),
(3, 'PROP-003', 'Local Comercial Estratégico sobre Av. San Martín', 'Local Comercial', 'Alquiler', 'Av. San Martín esquina Calle 5 #610', 'Equipetrol, Santa Cruz', 'Amplio local comercial en planta baja con vitrina de vidrio templado hacia avenida principal de alto flujo vehicular y peatonal. Ideal para franquicia gastronómica, showroom, entidad financiera o tienda insignia.', 2400.00, 'USD', 0, 2, 190.00, 4, 'prop_3.jpg', 'Publicada', 3, 1),
(4, 'PROP-004', 'Casa Familiar en Condominio Cerrado Las Palmas', 'Casa', 'Venta', 'Av. Doble Vía La Guardia Km 4, Condominio Las Palmas', 'Las Palmas, Santa Cruz', 'Acogedora vivienda familiar dentro de exclusivo condominio con parques infantiles, club house y vigilancia permanente. Jardín posterior privado, galería social, escritorio en planta baja y tres cómodos dormitorios en planta alta.', 235000.00, 'USD', 3, 3, 260.00, 2, 'prop_4.jpg', 'Publicada', 1, 2),
(5, 'PROP-005', 'Oficina Ejecutiva Amoblada en Centro Empresarial', 'Oficina', 'Alquiler', 'Av. Velarde #320, Edificio Corporativo Piso 6', 'Centro / Zona Sur, Santa Cruz', 'Oficina lista para operar con recepción, sala de reuniones privada, área colaborativa para 8 puestos de trabajo, climatización central, cocineta privada y parqueo subterráneo incluido en el canon.', 1150.00, 'USD', 0, 1, 85.00, 1, 'prop_5.jpg', 'Publicada', 4, 3),
(6, 'PROP-006', 'Terreno Residencial Urbanizado en Zona Urubó', 'Terreno', 'Venta', 'Camino al Urubó Km 3.5, Urbanización Colinas', 'Urubó, Santa Cruz', 'Terreno plano de topografía regular en urbanización consolidada con servicios básicos subterráneos, calles pavimentadas, laguna artificial y pórtico de ingreso controlado. Excelente plusvalía.', 125000.00, 'USD', 0, 0, 650.00, 0, 'prop_6.jpg', 'Publicada', 5, 3),
(7, 'PROP-007', 'Departamento Dúplex Acogedor en Barrio Sirari', 'Departamento', 'Anticrético', 'Calle Las Dalias #188, Barrio Sirari', 'Sirari, Santa Cruz', 'Dúplex moderno en zona residencial tranquila a pasos de cafés y parques. Planta baja con área social y cocina equipada; planta alta con dos dormitorios luminosos y terraza privada.', 55000.00, 'USD', 2, 2, 115.00, 1, 'prop_2.jpg', 'Registrada', 2, 1),
(8, 'PROP-008', 'Quinta Campestre con Árboles Frutales y Galería', 'Quinta', 'Venta', 'Carretera al Norte Km 14, Urbanización Los Tajibos', 'Zona Norte, Santa Cruz', 'Amplia propiedad campestre rodeada de naturaleza, con casa principal, cabaña de huéspedes, cancha polifuncional y arboleda.', 195000.00, 'USD', 3, 2, 1200.00, 4, 'prop_4.jpg', 'No Publicada', 5, 2);

INSERT INTO `publicaciones` (`id_publicacion`, `id_propiedad`, `id_agente_publicador`, `estado_publicacion`, `destacada`, `visitas`, `fecha_publicacion`, `fecha_retiro`, `observaciones`) VALUES
(1, 1, 1, 'Publicada', 1, 142, '2026-11-01 09:30:00', NULL, 'Publicación destacada en portada principal.'),
(2, 2, 2, 'Publicada', 1, 98, '2026-11-04 14:15:00', NULL, 'Inmueble con alta demanda en zona norte.'),
(3, 3, 1, 'Publicada', 1, 76, '2026-11-06 10:00:00', NULL, 'Disponible para entrega inmediata.'),
(4, 4, 2, 'Publicada', 0, 64, '2026-11-09 16:40:00', NULL, 'Documentación verificada.'),
(5, 5, 3, 'Publicada', 0, 51, '2026-11-12 11:20:00', NULL, 'Incluye expensas básicas.'),
(6, 6, 3, 'Publicada', 0, 83, '2026-11-14 15:10:00', NULL, 'Papeles al día en Derechos Reales.'),
(7, 8, 2, 'No Publicada', 0, 39, '2026-10-20 09:00:00', '2026-11-15 17:30:00', 'Retirada temporalmente por actualización de avalúo con el propietario.');

INSERT INTO `consultas_clientes` (`id_consulta`, `id_propiedad`, `id_agente`, `nombre_cliente`, `email_cliente`, `telefono_cliente`, `mensaje`, `estado`) VALUES
(1, 1, 1, 'Andrés Villarroel', 'andres.villarroel@correo.com', '+591 75012345', 'Buenos días, me gustaría agendar una visita para conocer la residencia en Equipetrol Norte este viernes por la tarde.', 'Pendiente'),
(2, 2, 2, 'Sofía Castellanos', 'sofia.castellanos@correo.com', '+591 76498765', 'Hola Valeria, quisiera saber si el precio del departamento en Torre Mirador es negociable y cuánto se paga de expensas mensuales.', 'Contactado'),
(3, 3, 1, 'Marcos Antelo', 'mantelo@empresa.com', '+591 77123987', 'Estamos buscando un local comercial sobre Av. San Martín para abrir una cafetería. ¿Podemos coordinar una inspección técnica?', 'Pendiente');

COMMIT;
