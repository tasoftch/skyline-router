# Skyline Router
The router package is used by any Skyline Kernel Application after launch to route a request or what ever to an action controller, that is able to produce a response.

The routing process is initialized by a RouteEvent.  
Register your routers as plugins to listen on the event manager.

The default routing workflow is:
- Application must create a route event
- The route event is triggered
- The routers need to route it to an action controller and a method of it (by default, other behaviour possible using different packages)
- The action controller gets instantiated and the method will be called

### Note!
This package does not register routers as plugins. It only provides several routers.