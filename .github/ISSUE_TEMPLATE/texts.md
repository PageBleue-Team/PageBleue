name: Problème de rédaction
about: Signalement d'erreurs d'orthographe
title: '[Orthographe]: '
labels: texts
assignees: @PageBleue-Team/PageBleue-Textes
body:
  - type: markdown
    attributes:
      value: |
        Merci de prendre le temps de remplir cela !
  - type: input
    id: page
    attributes:
      label: Quelle page ?
      description: Sur quelle page avez-vous vu l'erreur ?
      placeholder: ex. pagebleue.wstr.fr/list
    validations:
      required: true
  - type: textarea
    id: what-error
    attributes:
      label: Quelle erreur d'orthographe ?
      description: Comment aurions nous du rédiger ce texte ou mot ?
      placeholder: Dites nous comment nous corriger !
      value: "L'erreur est dans le texte..."
    validations:
      required: true
