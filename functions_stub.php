<?php

	 function pluginApp(
		string $abstract,
		array $parameters = []
	)
	{ return new $abstract($parameters); }

	 function publicPath(
		string $pluginName = null
	)
	{ return null; }

	 function pluginSetId(
	):int
	{ return null; }

