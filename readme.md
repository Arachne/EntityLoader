Arachne/EntityLoader
====

[![Build Status](https://img.shields.io/travis/Arachne/EntityLoader/master.svg?style=flat-square)](https://travis-ci.org/Arachne/EntityLoader/branches)
[![Coverage Status](https://img.shields.io/coveralls/Arachne/EntityLoader/master.svg?style=flat-square)](https://coveralls.io/github/Arachne/EntityLoader?branch=master)
[![Latest stable](https://img.shields.io/packagist/v/arachne/entity-loader.svg?style=flat-square)](https://packagist.org/packages/arachne/entity-loader)
[![Downloads this Month](https://img.shields.io/packagist/dm/arachne/entity-loader.svg?style=flat-square)](https://packagist.org/packages/arachne/entity-loader)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://github.com/Arachne/EntityLoader/blob/master/license.md)

Enables object parameters in nette/application.

```php
// Without EntityLoader
public function actionEdit($id)
{
	$article = $this->em->getRepository(Article::class)->find($id);
	if (! $article) {
		$this->error(); // 404
	}
	// ...
}

// With EntityLoader
public function actionEdit(Article $article)
{
	// ...
}
```

Documentation
----

- [Installation](docs/installation.md)
- [Doctrine integration](docs/doctrine-integration.md)
- [Custom filters](docs/custom-filters.md)
