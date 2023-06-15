<!DOCTYPE html>
<html lang="en">
  <!--
  USAGE:
  * Double-click to add cards
  * Click and drag to move cards around
  * Cards are contenteditable so you can change the text
  * ctrl+mousewheel to zoom in and zoom out (Won't work with Firefox unfortunately)
  

  BROKEN:
  * If the page is scrolled up or down position of things will be weird

  
  TODO:
  * Delete cards
  * Persist cards to localstorage or a file
  * Ability to draw lines between cards
  * Hold down space and click / drag to pan
  * Zoom based on mouse position
-->
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Syllogisms</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap"
      rel="stylesheet"
    />
    <style>
      body {
        font-family: Libre Baskerville, system-ui, -apple-system,
          BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell,
          "Open Sans", "Helvetica Neue", sans-serif;
        background-color: rgb(246, 242, 228);
        color: rgb(74, 46, 22);
      }

      .card {
        border: 1px solid rgb(192, 153, 98);
        border-radius: 5px;
        position: absolute;
        padding: 5px;
        background-color: rgb(223, 210, 175);
      }

      .card:focus-visible {
        outline: none;
      }

      .card.selected {
        border: 2px solid rgb(255 242 209);
      }
    </style>
  </head>

  <body>
    <div class="mount-here"></div>

    <script id="component_template_library" type="text/javascript">
      function component_template(html_template, init_handlers = {}) {
        const get_containers = (selector) => {
          if (typeof selector === "string") {
            return document.querySelectorAll(selector);
          } else if (typeof selector.length === "undefined") {
            return [selector];
          }

          return selector;
        };

        const render_template = (props) => {
          return typeof html_template === "function"
            ? html_template(...props)
            : html_template;
        };

        const component_template_id = crypto.randomUUID();

        if (init_handlers.__global) {
          init_handlers.__global();
        }

        return {
          append_to(selector, ...props) {
            for (let container of get_containers(selector)) {
              let div = document.createElement("div");
              this.render(div, ...props);
              for (let item of [...div.childNodes]) container.appendChild(item);
            }
          },

          render(selector, ...props) {
            for (let container of get_containers(selector)) {
              container.innerHTML = render_template(props);

              for (let [target_selector, init] of Object.entries(
                init_handlers
              )) {
                if (target_selector === "__global") continue;

                for (let target of container.querySelectorAll(
                  target_selector
                )) {
                  init(target, ...props);
                }
              }
            }
          },
        };
      }
    </script>

    <script id="component_templates" type="text/javascript">
      let _card = component_template(
        ({ id, message, x, y }) => /* html */ `
              <div 
                id="${id}"
                class="card ${cls({
                  selected: STATE.selected_card_id === id,
                })}" 
                contenteditable 
                style="left: ${x}px; top: ${y}px"
              >${message}</div>
          `,
        {
          __global: () => {
            document.addEventListener("mouseup", (e) => {
              if (STATE.dragging_card_id) {
                STATE.dragging_card_id = null;
                e.preventDefault();
              }
            });

            document.addEventListener("mousemove", (e) => {
              if (STATE.dragging_card_id) {
                let mouse = new CursorPoint(e.pageX, e.pageY);
                let new_position = new Point(
                  mouse.x - STATE.dragging_offset.x,
                  mouse.y - STATE.dragging_offset.y
                );

                STATE.cards.lookup[STATE.dragging_card_id].x = new_position.x;
                STATE.cards.lookup[STATE.dragging_card_id].y = new_position.y;

                document
                  .getElementById(STATE.dragging_card_id)
                  .setAttribute(
                    "style",
                    `left: ${new_position.x}px; top: ${new_position.y}px`
                  );
              }
            });

            document.addEventListener("click", (e) => {
              if (!e.target.classList.contains("card"))
                remove_class(".card.selected", "selected");
            });

            document.addEventListener("dblclick", (e) => {
              if (e.target.classList.contains("card")) return;
              let new_id = crypto.randomUUID();
              let click = new CursorPoint(e.pageX, e.pageY);

              STATE.cards.add_item({
                id: new_id,
                message: random_words(3),
                x: click.x,
                y: click.y,
              });
              STATE.selected_card_id = new_id;
              render();
            });
          },
          ".card": (el, { id }) => {
            const select_card = (id) => {
              STATE.select_card_id = id;
              remove_class(".card.selected", "selected");
              document.getElementById(id).classList.add("selected");
            };

            el.addEventListener("keyup", () => {
              STATE.cards.lookup[id].message = el.innerHTML;
            });

            el.addEventListener("mousedown", (e) => {
              var rect = e.target.getBoundingClientRect();
              STATE.dragging_card_id = id;

              let click = new CursorPoint(e.pageX, e.pageY);

              STATE.dragging_offset = new Point(
                click.x - rect.left,
                click.y - rect.top
              );

              select_card(id);
            });

            el.addEventListener("click", (e) => {
              select_card(id);
            });
          },
        }
      );
    </script>

    <script id="helper_functions" type="text/javascript">
      const ipsum = `
    Sed ut perspiciatis unde omnis iste natus 
    error sit voluptatem accusantium doloremque 
    laudantium, totam rem aperiam, eaque ipsa 
    quae ab illo inventore veritatis et quasi 
    architecto beatae vitae dicta sunt explicabo. 
    Nemo enim ipsam voluptatem quia voluptas 
    sit aspernatur aut odit aut fugit, 
    sed quia consequuntur magni dolores eos 
    qui ratione voluptatem sequi nesciunt. 
    Neque porro quisquam est, qui dolorem ipsum 
    quia dolor sit amet, consectetur, adipisci velit, 
    sed quia non numquam eius modi tempora incidunt 
    ut labore et dolore magnam aliquam quaerat 
    voluptatem. Ut enim ad minima veniam, quis 
    nostrum exercitationem ullam corporis suscipit 
    laboriosam, nisi ut aliquid ex ea commodi 
    consequatur? Quis autem vel eum iure 
    reprehenderit qui in ea voluptate velit 
    esse quam nihil molestiae consequatur, vel 
    illum qui dolorem eum fugiat quo voluptas 
    nulla pariatur?`
        .trim()
        .replace(/[^\s\w]/g, "")
        .split(/\s+/);

      function random_words(count = 4) {
        let start = random_int(0, ipsum.length - (1 + count));

        return ipsum.slice(start, start + count).join(" ");
      }

      class Point {
        constructor(x, y) {
          this.x = x;
          this.y = y;
        }
      }

      class CursorPoint {
        constructor(x, y) {
          let zoom_multiplier = STATE.zoom / 100;

          this.x = x / zoom_multiplier;
          this.y = y / zoom_multiplier;
        }
      }

      function log(...args) {
        console.log(...args);
      }

      function str_log(...args) {
        console.log(JSON.stringify(args, null, 2));
      }

      class ListWithLookup {
        constructor(lookup_key, initial_list = []) {
          this.list = initial_list;
          this.lookup_key = lookup_key;
          this.lookup ||= {};

          this.generate_lookup();
        }

        generate_lookup() {
          for (let item of this.list) {
            this.lookup[item[this.lookup_key]] = item;
          }
        }

        add_item(item) {
          this.list.push(item);
          this.lookup[item[this.lookup_key]] = item;
        }

        delete_item(item) {
          this.list = this.list.filter(
            (l) => l[this.lookup_key] !== item[this.lookup_key]
          );
          delete this.lookup[item[this.lookup_key]];
        }
      }

      function random_int(min, max) {
        return Math.round(Math.random() * (max - min) + min);
      }

      function remove_class(selector, css_class) {
        document
          .querySelectorAll(selector)
          .forEach((el) => el.classList.remove(css_class));
      }

      function random_element(list) {
        return list[random_int(0, list.length - 1)];
      }

      function clear_div_content(selector) {
        for (let el of document.querySelectorAll(selector)) {
          el.innerHTML = "";
        }
      }

      function cls(obj) {
        let css_classes = [];
        for (let key of Object.keys(obj)) {
          if (obj[key]) css_classes.push(key);
        }

        return css_classes.join(" ");
      }
    </script>

    <script>
      let STATE = {
        cards: new ListWithLookup("id"),
        zoom: 100,
      };

      const update_zoom = (amount) => {
        if (amount > 150) amount = 150;
        if (amount < 10) amount = 10;
        STATE.zoom = amount;

        document
          .querySelector("body")
          .setAttribute("style", `zoom: ${amount}%`);
      };

      window.addEventListener("wheel", (e) => {
        if (e.ctrlKey) {
          update_zoom(STATE.zoom + e.deltaY * 0.02);
        }
      });

      function render() {
        clear_div_content(".mount-here");
        for (let card of STATE.cards.list) {
          _card.append_to(".mount-here", card);
        }
      }

      render();
    </script>
  </body>
</html>
