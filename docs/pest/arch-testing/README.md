# Tests d'architecture avec Pest

Support de la session 9 du module [TDD en pratique · 20 heures](../../tdd/module-20h/README.md).

Les tests d'architecture ne remplacent pas les tests de comportement. Ils vérifient la forme du code: les dépendances, les frontières, les conventions et les types.

## Ce que `arch()` vérifie

- les namespaces et les classes;
- les dépendances entre namespaces;
- l'héritage, les interfaces et les traits;
- les méthodes et les propriétés;
- les attributs et la documentation;
- la taille des fichiers et le casing.

## Exemples

```php
arch('models are persistence only')
    ->expect('App\Models')
    ->not->toUse([
        'Illuminate\Http',
        'Illuminate\Support\Facades\Http',
    ]);

arch('enums stay framework agnostic')
    ->expect('App\Enums')
    ->not->toUse('Illuminate');

arch('notifications provide an email representation')
    ->expect('App\Notifications')
    ->toHaveMethod('toMail');
```

## Presets

```php
arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();
arch()->preset()->strict();
```

Un preset est un point de départ. Toute exception doit être nommée avec `ignoring()` et justifiée.

## Ce que `arch()` ne fait pas

- Il ne vérifie pas le comportement.
- Il ne remplace pas un test de feature.
- Il ne détecte pas un bug de logique.
- Il ne rend pas un mauvais design bon.

## Dans le module TDD

La session 9 demande cinq règles:

1. Les modèles ne dépendent pas de la couche HTTP.
2. Les enums ne dépendent d'aucun framework.
3. Les notifications exposent `toMail()`.
4. Les contrôleurs sont `final` et suffixés `Controller`.
5. Aucun `dd`, `dump`, `die` dans `App`.

Chaque règle doit être justifiée en une phrase.

## Sources

- [Pest, Architecture Testing](https://pestphp.com/docs/arch-testing)
- [Pest, Migration depuis PHPUnit](https://pestphp.com/docs/migrating-from-phpunit-guide)
- [Pest, Expectations](https://pestphp.com/docs/expectations)
- [PHPUnit, Assertions](https://docs.phpunit.de/en/13.4/assertions.html)
