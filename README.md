# Skyline Router
The router package is used by any Skyline Kernel Application to assign any incoming request to a module and an action of that module.

The routing process is initialized by a RouteEvent.  
Register your routers as plugins to listen to the event manager.

The default routing workflow is:
- Application must create a route event

