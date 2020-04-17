# queo Site Kickstarter

## Introduction

This package is largely based on the native neos site kickstarter package in neos core. The goal of this package is to give the user the posibility to use new afx style or the old school fluid way of rendering pages and components in NEOS.

## Usage

You can kickstart a site package by using the following command:

```
flow quickstart:site <packageKey> <siteName>
```
then you get asked which generator service you want to use. Currently two different types are coming with this package, the AfxTemplateGenerator and the FluidTemplateGenerator.

## Custom template generator

You can develop your own template generator by just extending the `AbstractSitePackageGenerator`. The command notice every present generator service which is a subclass of `AbstractSitePackageGenerator` and adds it to the selection.

## Tests

TODO