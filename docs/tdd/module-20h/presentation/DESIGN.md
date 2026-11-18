# Design du deck

Le deck est un système, pas une collection de diapositives. Toute modification doit respecter les règles ci-dessous.

## Cadre

- Scène: 1600 × 900.
- Marge de sécurité: 96 px à gauche et à droite.
- Grille: 12 colonnes, gouttière de 24 px.
- Zones verticales:
  - en-tête: 40 px;
  - titre: 140 à 330 px;
  - contenu: 354 à 824 px;
  - pied de page: 860 px.

## Typographie

| Usage | Taille |
| --- | --- |
| Titre de couverture | 104 px |
| Titre de diapositive | 48 px |
| Sous-titre | 18 px |
| Corps | 18 px |
| Petit texte | 13 px |
| Légende mono | 11 px |
| Code | 13 px, 11.5 px pour les blocs denses |

Ne pas introduire une septième taille. Le code et les tableaux ont leur propre échelle.

## Modèles

Cinq modèles seulement:

1. **Cover**: titre, phrase, une preuve visuelle.
2. **Statement**: kicker, titre, une phrase, un visuel ou une citation.
3. **Split**: deux colonnes, texte et code, ou texte et liste.
4. **Data**: tableau ou grille de données.
5. **Divider**: numéral, titre de partie, une phrase.

Une diapositive qui ne rentre dans aucun modèle doit être coupée en deux.

## Budget de contenu

- 60 mots maximum par diapositive.
- 4 puces maximum.
- 10 lignes de code maximum pour un fragment, 20 pour un exemple complet.
- 8 lignes de tableau maximum.
- 3 tailles de texte maximum par diapositive.
- Un seul accent visuel: une bordure, un chiffre ou un bloc de code.

Si le contenu dépasse, couper ou diviser. Ne pas réduire la police.

## Règles visuelles

- Cartes: un seul style, bord 1 px, fond papier translucide, pas d'ombre.
- Code: seul élément sombre avec une ombre légère.
- Couleurs: papier, encre, accent, vert, gris. Le vert est réservé à l'étape verte.
- Pas de rotation, pas de dégradé décoratif, pas de bordure supérieure sur toutes les cartes.
- Le pied de page reste discret. Les contrôles sont un secours, pas un élément de composition.

## Ajouter une diapositive

Dans `slides.js`, une diapositive standard commence par `titleBlock(...)`. Le helper ferme le bloc de titre et pose le marqueur `<!--slide-content-->`. `deck.js` utilise ce marqueur pour créer le bloc de contenu et le centrer dans la zone prévue.

```js
{
  section: 'La boucle',
  title: 'Titre court',
  html: `${titleBlock('KICKER', 'Titre.', 'Une phrase.', true)}
    <div class="...">...</div>
    <p class="footnote">Note de bas de diapositive.</p>`,
}
```

Les modèles `cover`, `divider` et `closing` utilisent un `html` complet et `chrome: false`.

## Vérification

Avant de livrer:

1. `node --check slides.js` et `node --check deck.js`.
2. Captures à 1600 × 900 pour chaque diapositive.
3. Vérifier les débordements sous le pied de page.
4. Vérifier la lisibilité à 1280 × 720.
5. Exporter le PDF et vérifier le nombre de pages.
6. Vérifier qu'aucune diapositive ne dépasse le budget de contenu.
