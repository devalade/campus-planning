# Quiz de vérification

Quiz formatif, à faire à la fin des sessions 2, 5 et 9. Une réponse correcte vaut 1 point. Le quiz compte dans les 10% de participation.

## Questions

1. Quelles sont les trois lois du TDD?
2. Pourquoi un test doit-il échouer avant l'écriture du code de production?
3. Quelle est la différence entre `toBe()` et `toEqual()` en Pest?
4. Que signifie Arrange, Act, Assert?
5. Quelle est la différence entre un stub et un mock?
6. Pourquoi préférer `Notification::fake()` à un vrai serveur SMTP dans un test?
7. Que vérifie un test d'architecture?
8. Pourquoi ne pas tester une méthode privée?
9. Qu'est-ce que la triangulation?
10. Qu'est-ce qu'un walking skeleton?

## Réponses attendues

1. Pas de code de production sans un test qui échoue. Pas plus de test qu'il n'en faut pour échouer. Pas plus de code qu'il n'en faut pour passer.
2. Parce que l'échec prouve que le test observe bien le comportement visé. Un test qui passe tout de suite ne prouve rien.
3. `toBe()` utilise `assertSame()`: identité stricte. `toEqual()` utilise `assertEquals()`: égalité souple.
4. Préparer les données, exécuter l'action, vérifier le résultat observable.
5. Un stub retourne une valeur préparée. Un mock vérifie qu'un appel a eu lieu, avec quels arguments. Un mock teste une interaction; un stub fournit une réponse.
6. Parce qu'un test ne doit pas dépendre du réseau, d'un serveur externe ou d'un état partagé. Le fake rend l'effet observable et déterministe.
7. Il vérifie la forme du code: dépendances, frontières, héritage, conventions. Il ne vérifie pas le comportement.
8. Parce qu'une méthode privée est un détail d'implémentation. Le test deviendrait fragile au moindre refactor.
9. Ajouter un cas qui force une généralisation. On remplace un faux code par une vraie règle quand un deuxième ou troisième exemple l'exige.
10. La plus petite tranche verticale qui traverse l'application et prouve que le chemin fonctionne, souvent sans logique métier complète.
