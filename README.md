
A [PostgreSQL] driver for [Kohana]'s [Database] module.

## Usage
- [PostgreSQL](http://www.postgresql.org/)
- [Kohana](http://kohanaframework.org/)
- [Kohana Database](http://github.com/kohana/database)

## Converters and Types
(17-Jun-13) Added support for field [converters](http://pomm.coolkeums.org/documentation/manual#database-and-converters) and types, copied straight from [Pomm](http://pomm.coolkeums.org), the "PHP Object Model Manager for Postgresql" by Grégoire HUBERT and contributors.

From Pomm's readme:

> Data can be [converted](http://pomm.coolkeums.org/documentation/manual#database-and-converters) from/to Postgresql. Boolean in Pg are boolean in PHP, [arrays](http://www.postgresql.org/docs/8.4/static/arrays.html)
in Pg are arrays in PHP, [geometric types](http://www.postgresql.org/docs/8.4/static/datatype-geometric.html) are converted into geometric PHP objects. Of course this is extensible and custom database types can be converted into custom PHP classes. Almost all standard and geometric types are supported plus [range](http://www.postgresql.org/docs/9.2/static/rangetypes.html), [HStore](http://www.postgresql.org/docs/8.4/static/hstore.html) and [ltree](http://www.postgresql.org/docs/8.4/static/ltree.html) extensions.