# Wireguard pfSense Plugin

> WireGuard® is an extremely simple yet fast and modern VPN that utilizes state-of-the-art cryptography. It aims to be faster, simpler, leaner, and more useful than IPsec, while avoiding the massive headache. It intends to be considerably more performant than OpenVPN. WireGuard is designed as a general purpose VPN for running on embedded interfaces and super computers alike, fit for many different circumstances. Initially released for the Linux kernel, it is now cross-platform and widely deployable. It is currently under heavy development, but already it might be regarded as the most secure, easiest to use, and simplest VPN solution in the industry.

[More information about wireguard](https://www.wireguard.com/)

This plugin makes wireguard available via the pfSense Webinterface.

# Disclaimer

Wireguard it self does recommend to not use the software in production.
Further the pfSense plugin is in an early alpha state and should be used only for testing or educational purposes.

# Installation

To use wireguard on pfSense folloing pakages has to be installed manually:

  [wireguard](http://pkg.freebsd.org/FreeBSD:11:amd64/latest/All/wireguard-0.0.20190406.txz)

  [wireguard-go](http://pkg.freebsd.org/FreeBSD:11:amd64/latest/All/wireguard-go-0.0.20190409.txz)

After copy both wireguard pakages and the plugin pakage to the pfSense you can install it as follows:
```	
pkg install wireguard-0.0.20190406.txz wireguard-go-0.0.20190409.txz pfSense-pkg-wireguard-0.1.txz
```

# Usage

The Wireguard Plugin can be found under -> VPN -> Wireguard.
After creating a VPN Connection the pfSense interface can be configured.
Assign the new interface. After assigning the connection will not work any more. It has to be configured correctly.

The interface name can be chosen independent from the wireguard configuration and will be used in the other pfSense settings like Firewall Rules.
Further only the MTU has to be set to 1420 byte because of the wireguard standard. No further changes has to be made in the interface configuration.
After save and apply the wireguard connection has to be reloaded, because of wrong pfSense configuration.

Then also Firewall rules can be configured and the connection should work as intended.

# Roadmap

* Import functionality
* Nice and shiny status page
* Log Page
* Improving pfSense integration

# Issues

Interface is deleted after reboot, if the wireguard interface is not running. To get the firewall rules back, you can assign the interface with the same name.

