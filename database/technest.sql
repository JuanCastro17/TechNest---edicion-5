-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 18-06-2026 a las 20:33:41
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `technest`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` varchar(50) NOT NULL,
  `imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio`, `stock`, `imagen`) VALUES
(1, 'Laptop Asus', 400000.00, '12', 'default.jpg'),
(2, 'cubos 3x3', 10000.00, '1', 'default.jpg'),
(3, 'cubos 3x3', 10000.00, '1', 'default.jpg'),
(6, 'lavadora', 50000.00, '12', 'default.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `celular` varchar(15) DEFAULT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT 'user.ico',
  `rol` varchar(20) DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `celular`, `usuario`, `password`, `avatar`, `rol`) VALUES
(1, 'pepito', 'vargas', 'pepito@gmail.com', '', 'Gato19', '$2y$10$MG7GWCIpq6XyVnqSTJcrTeisrW2h1sJQcnTbDcCfcKUKt8Ny42cTG', 'avatar_1_1780702512.jpg', 'usuario'),
(2, 'luis', 'carlos', 'luis@gmail.com', '', 'luisito18', '$2y$10$6FgU1Ro2EX6h2vot86l.CuGYfPilo/pltrc..Gx6mo/SHAc1qp5pK', 'avatar_2_1780518645.jpeg', 'usuario'),
(3, '', '', 'admin@technest.com', NULL, 'Leonardo Cooper', '$2y$10$ckpGKeoFqqXxVgsM707CIeBkLE5Qoo7Qr0wGcqb222OxtUYQLdp/a', 'perro-admin.png', 'admin'),
(4, 'sofia', 'contreras', 'livnisofia.100@gmail.com', '', 'Arrozconleche27', '$2y$10$9RKAR6NzfmAUhKzB7lPo0epgGPT05fnkCfSZicB4yTEwJt63Lt1uO', 'avatar_4_1780685515.jpg', 'usuario'),
(5, 'juan', 'salazar', 'usuario25@gmail.com', NULL, 'juanC', '$2y$10$2KbaEuXi69NVtXyIT.5PB.tbRjZbCygA2ZhXEXTG86w3pJML1MuJm', 'user.ico', 'usuario');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
