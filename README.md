Sulu Document Manager
=====================

[![](https://travis-ci.org/sulu-io/sulu-document-manager.png?branch=master)](https://travis-ci.org/sulu-io/sulu-document-manager) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sulu-io/sulu-document-manager/badges/quality-score.png?s=a4e66cebefa4fb6f55f50066d516dc4ab9ba3d86)](https://scrutinizer-ci.com/g/sulu-io/sulu-document-manager/)


100% event driven PHPCR based document manager.

Features:

- Internationalized by default
- Persist and hydration events are "option" aware.
- Lazy loading and proxies
- No unit of work. Changes are persisted directly to nodes and `flush()`
  flushes the PHPCR session.
- Highly extensible (the DocumentManager just fires events)
- Extremely decoupled
- Profileable subscribers are automatically profiled when in `debug` mode.


This library is currently under development.
