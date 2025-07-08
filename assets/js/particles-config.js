particlesJS("particles-js", {
    particles: {
        number: {
            value: 80,
            density: {
                enable: true,
                value_area: 800
            }
        },
        color: {
            value: ["#ff0000", "#00ff00", "#0000ff", "#ffffff"]
        },
        shape: {
            type: ["polygon", "edge", "star"],
            polygon: {
                nb_sides: 6
            },
            stroke: {
                width: 0.5,
                color: "#000000"
            }
        },
        opacity: {
            value: 0.4,
            random: true,
            anim: {
                enable: true,
                speed: 0.5,
                opacity_min: 0.2,
                sync: false
            }
        },
        size: {
            value: 4,
            random: true,
            anim: {
                enable: true,
                speed: 3,
                size_min: 2,
                sync: false
            }
        },
        line_linked: {
            enable: true,
            distance: 150,
            color: "#ffffff",
            opacity: 0.2,
            width: 0.5,
            triangles: {
                enable: true,
                color: "#ff0000",
                opacity: 0.05
            }
        },
        move: {
            enable: true,
            speed: 2,
            direction: "none",
            random: true,
            straight: false,
            out_mode: "bounce",
            bounce: true,
            attract: {
                enable: true,
                rotateX: 600,
                rotateY: 1200
            }
        }
    },
    interactivity: {
        detect_on: "canvas",
        events: {
            onhover: {
                enable: true,
                mode: ["grab", "bubble"]
            },
            onclick: {
                enable: true,
                mode: "push"
            },
            resize: true
        },
        modes: {
            grab: {
                distance: 200,
                line_linked: {
                    opacity: 0.3
                }
            },
            bubble: {
                distance: 200,
                size: 8,
                duration: 1.5,
                opacity: 0.4,
                speed: 2
            },
            push: {
                particles_nb: 4
            }
        }
    },
    retina_detect: true
}); 