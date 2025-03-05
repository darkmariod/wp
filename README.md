<hr />

<h1>Hola mundo desde Wordpress con Docker y docker-compose</h1>
<h1>Aprovisionamiento para despliegue en AWS con EC2 (Elastic Compute Cloud) y RDS (Relational Database Service )</h1>
<h2>PHP 8.3.15</h2>
<h2>Symfony 7</h2>
<h2>Docker 27.2.0</h2>
<h2>Docker compose 3.8</h2> 
<h3>Todo preparado para implementar un CD/CI bajo algún servidor de integración continua como Jenkins por ejemplo</h3>

#instalar contenedor<br/>
<code>docker-compose up --build</code>
<hr />
#entrar a la consola
<br/>
<code>docker exec -it peligro-wordpress-app bash</code>
<hr />
#detener proyecto
<br/>
<code>docker-compose down</code>